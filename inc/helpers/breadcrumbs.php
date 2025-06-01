<?php
/**
 * @package ToroAG
 * @subpackage Helpers.Breadcrumbs
 */
// Breadcrumbs Yoast SEO
// aggiunge l'id "breadcrumbs" al breadcrumb di Yoast SEO

function divi_breadcrumb_shortcode() {
    if ( function_exists('yoast_breadcrumb') ) {
      return yoast_breadcrumb('<p id="breadcrumbs">','</p>', false);
    }
  }
  add_shortcode('my_breadcrumbs', 'divi_breadcrumb_shortcode');
/**
 * Shortcode [my_breadcrumbs]: integra Yoast Breadcrumbs
 * e per le tassonomie 'tipo_di_prodotto' e 'coltura'
 * aggiunge sempre il link alla pagina archivio tradotta.
 */
function toro_ag_divi_breadcrumb_shortcode() {
    if ( ! function_exists( 'yoast_breadcrumb' ) ) {
        return ''; // Yoast non attivo
    }

    // Se siamo in un termine di tipo_di_prodotto o coltura
    if ( is_tax( array( 'tipo_di_prodotto', 'coltura' ) ) ) {
        $taxonomy = get_query_var( 'taxonomy' ); // 'tipo_di_prodotto' o 'coltura'

        // Mappa slug pagina archivio per ciascuna tassonomia
        $map = array(
            'tipo_di_prodotto' => 'prodotti',
            'coltura'          => 'applicazioni',
        );

        // 1) Home
        $breadcrumbs = '<p id="breadcrumbs">';
        $breadcrumbs .= '<a href="' . home_url() . '">' . esc_html__( 'Home', 'toro-ag' ) . '</a>';

        // 2) Link alla pagina archivio (tradotta con WPML)
        if ( isset( $map[ $taxonomy ] ) ) {
            $slug = $map[ $taxonomy ];
            $page = get_page_by_path( $slug, OBJECT, 'page' );
            if ( $page ) {
                // ottiene ID tradotto nella lingua corrente
                $page_id = apply_filters( 'wpml_object_id', $page->ID, 'page', true, ICL_LANGUAGE_CODE );
                $breadcrumbs .= ' &raquo; <a href="' . get_permalink( $page_id ) . '">'
                              . esc_html( get_the_title( $page_id ) ) . '</a>';
            }
        }

        // 3) Term name
        $breadcrumbs .= ' &raquo; ' . single_term_title( '', false );
        $breadcrumbs .= '</p>';

        return $breadcrumbs;
    }

    // fallback Yoast normale per tutte le altre pagine
    return yoast_breadcrumb( '<p id="breadcrumbs">', '</p>', false );
}
remove_shortcode( 'my_breadcrumbs' );
add_shortcode(   'my_breadcrumbs', 'toro_ag_divi_breadcrumb_shortcode' );

/**
 * Filtra il permalink del CPT "prodotto" sostituendo %tipo_di_prodotto%
 * con lo slug reale del termine di 'tipo_di_prodotto' assegnato al post.
 */
function toro_ag_prodotto_permalink( $post_link, $post ) {
    // Applico solo ai post type "prodotto"
    if ( $post->post_type !== 'prodotto' ) {
        return $post_link;
    }

    // Prendo i termini assegnati alla tassonomia 'tipo_di_prodotto'
    $terms = wp_get_post_terms( $post->ID, 'tipo_di_prodotto' );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        // Se non ci sono termini, metto un fallback (lo slug "nonspecificato" oppure lascio il permalink di default)
        // Qui potete decidere: 
        //   - tornare $post_link così com’è, oppure 
        //   - sostituire %tipo_di_prodotto% con 'nonspecificato' per evitare che compaia il placeholder.
        return str_replace( '%tipo_di_prodotto%', 'nonspecificato', $post_link );
    }

    // Prendo il primo termine (se ne avete più di uno, potete decidere come comportarvi)
    $term = $terms[0];
    // Recupero lo slug del termine (in lingua corrente, WPML si occupa di restituire lo slug tradotto)
    $term_slug = $term->slug;

    // Sostituisco nel permalink "%tipo_di_prodotto%" con il vero slug
    $post_link = str_replace( '%tipo_di_prodotto%', $term_slug, $post_link );

    return $post_link;
}
add_filter( 'post_type_link', 'toro_ag_prodotto_permalink', 10, 2 );

// 1) (Opzionale, se Pods non l’ha già registrata)  
add_action( 'init', function() {
    add_rewrite_tag( '%tipo_di_prodotto%', '([^/]+)', 'tipo_di_prodotto=' );
}, 5 );

// 2) Filtro per sostituire il placeholder con il vero slug
add_filter( 'post_type_link', function( $post_link, $post ) {
    if ( $post->post_type !== 'prodotto' ) {
        return $post_link;
    }
    $terms = wp_get_post_terms( $post->ID, 'tipo_di_prodotto' );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return str_replace( '%tipo_di_prodotto%', 'nessuna-categoria', $post_link );
    }
    return str_replace( '%tipo_di_prodotto%', $terms[0]->slug, $post_link );
}, 10, 2 );

// 3) (Se dopo il flush continua a mancare) Aggiungi la regola manuale di rewrite
add_action( 'init', function() {
    add_rewrite_rule(
        '^prodotti/([^/]+)/([^/]+)/?$',
        'index.php?post_type=prodotto&tipo_di_prodotto=$matches[1]&name=$matches[2]',
        'top'
    );
}, 10 );
