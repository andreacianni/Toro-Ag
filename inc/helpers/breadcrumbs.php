<?php
/**
 * @package ToroAG
 * @subpackage Helpers.Breadcrumbs
 */

/**
 * Shortcode [my_breadcrumbs]: 
 * - Se siamo in un singolo 'prodotto': Home » Prodotti » Termine » Titolo Prodotto.
 * - Se siamo in un termine di 'tipo_di_prodotto' o 'coltura': Home » Archivio » Termine.
 * - Altrimenti: fallback a Yoast SEO breadcrumb.
 */
function toro_ag_divi_breadcrumb_shortcode() {
    if ( ! function_exists( 'yoast_breadcrumb' ) ) {
        return '';
    }

    // 1) Caso: sono in un singolo Prodotto
    if ( is_singular( 'prodotto' ) ) {
        global $post;
        $breadcrumbs  = '<p id="breadcrumbs">';
        // Home
        $breadcrumbs .= '<a href="' . home_url() . '">' . esc_html__( 'Home', 'toro-ag' ) . '</a>';

        // Pagina Archivio "Prodotti" (IT slug: prodotti)
        $pagina_it = get_page_by_path( 'prodotti', OBJECT, 'page' );
        if ( $pagina_it ) {
            $pagina_id = apply_filters( 'wpml_object_id', $pagina_it->ID, 'page', true, ICL_LANGUAGE_CODE );
            if ( $pagina_id ) {
                $breadcrumbs .= ' &raquo; <a href="' . esc_url( get_permalink( $pagina_id ) ) . '">' 
                              . esc_html( get_the_title( $pagina_id ) ) . '</a>';
            }
        }

        // Termine di tipo_di_prodotto associato
        $terms = wp_get_post_terms( $post->ID, 'tipo_di_prodotto' );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $term = $terms[0];
            $term_link = get_term_link( $term, 'tipo_di_prodotto' );
            if ( ! is_wp_error( $term_link ) ) {
                $breadcrumbs .= ' &raquo; <a href="' . esc_url( $term_link ) . '">' 
                              . esc_html( $term->name ) . '</a>';
            }
        }

        // Titolo del Prodotto (ultimo livello, senza link)
        $breadcrumbs .= ' &raquo; ' . get_the_title( $post->ID );
        $breadcrumbs .= '</p>';

        return $breadcrumbs;
    }

    // 2) Caso: siamo in un termine di 'tipo_di_prodotto' o 'coltura'
    if ( is_tax( array( 'tipo_di_prodotto', 'coltura' ) ) ) {
        $taxonomy = get_query_var( 'taxonomy' );
        $map = array(
            'tipo_di_prodotto' => 'prodotti',
            'coltura'          => 'applicazioni',
        );
        $breadcrumbs  = '<p id="breadcrumbs">';
        $breadcrumbs .= '<a href="' . home_url() . '">' . esc_html__( 'Home', 'toro-ag' ) . '</a>';

        if ( isset( $map[ $taxonomy ] ) ) {
            $slug_it = $map[ $taxonomy ];
            $page_it = get_page_by_path( $slug_it, OBJECT, 'page' );
            if ( $page_it ) {
                $page_id_current = apply_filters( 'wpml_object_id', $page_it->ID, 'page', true, ICL_LANGUAGE_CODE );
                if ( $page_id_current ) {
                    $breadcrumbs .= ' &raquo; <a href="' 
                                  . esc_url( get_permalink( $page_id_current ) ) . '">' 
                                  . esc_html( get_the_title( $page_id_current ) ) . '</a>';
                }
            }
        }

        // Nome del termine corrente
        $breadcrumbs .= ' &raquo; ' . single_term_title( '', false );
        $breadcrumbs .= '</p>';

        return $breadcrumbs;
    }

    // 3) Fallback: Briciola generata da Yoast SEO
    return yoast_breadcrumb( '<p id="breadcrumbs">', '</p>', false );
}
remove_shortcode( 'my_breadcrumbs' );
add_shortcode( 'my_breadcrumbs', 'toro_ag_divi_breadcrumb_shortcode' );


/**
 * Filtra il permalink del CPT "prodotto" sostituendo %tipo_di_prodotto%
 * con lo slug reale del termine di 'tipo_di_prodotto' assegnato al post.
 */
function toro_ag_prodotto_permalink( $post_link, $post ) {
    if ( $post->post_type !== 'prodotto' ) {
        return $post_link;
    }

    $terms = wp_get_post_terms( $post->ID, 'tipo_di_prodotto' );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return str_replace( '%tipo_di_prodotto%', 'nessuna-categoria', $post_link );
    }

    $term_slug = $terms[0]->slug;
    return str_replace( '%tipo_di_prodotto%', $term_slug, $post_link );
}
add_filter( 'post_type_link', 'toro_ag_prodotto_permalink', 10, 2 );

/**
 * (Opzionale) Registrazione della rewrite tag %tipo_di_prodotto%
 */
add_action( 'init', function() {
    add_rewrite_tag( '%tipo_di_prodotto%', '([^/]+)', 'tipo_di_prodotto=' );
}, 5 );

/**
 * (Eventuale) Rewrite Rule manuale per /prodotti/SLUG-TERMINE/SLUG-PRODOTTO/
 */
add_action( 'init', function() {
    add_rewrite_rule(
        '^prodotti/([^/]+)/([^/]+)/?$', 
        'index.php?post_type=prodotto&tipo_di_prodotto=$matches[1]&name=$matches[2]', 
        'top'
    );
}, 10 );
