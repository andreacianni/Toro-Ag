<?php
// Ottieni il termine corrente della tassonomia "coltura"
$coltura_term = get_queried_object();

// Controlla che siamo davvero su un termine della tassonomia "coltura"
if ($coltura_term && $coltura_term->taxonomy === 'coltura') {
    // Recupera tutti i termini della tassonomia "tipo_di_prodotto"
    $tipi_prodotto = get_terms(array(
        'taxonomy' => 'tipo_di_prodotto',
        'hide_empty' => false // li vogliamo tutti, filtriamo dopo
    ));

    if (!empty($tipi_prodotto) && !is_wp_error($tipi_prodotto)) {
        foreach ($tipi_prodotto as $tipo) {
            // Query per cercare prodotti con entrambi i termini
            $args = array(
                'post_type' => 'prodotto',
                'posts_per_page' => -1,
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'coltura',
                        'field' => 'term_id',
                        'terms' => $coltura_term->term_id
                    ),
                    array(
                        'taxonomy' => 'tipo_di_prodotto',
                        'field' => 'term_id',
                        'terms' => $tipo->term_id
                    )
                )
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                // Mostra il titolo del tipo di prodotto con link
                echo '<h3><a href="' . esc_url(get_term_link($tipo)) . '">' . esc_html($tipo->name) . '</a></h3>';
                echo '<ul>';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
                }
                echo '</ul>';
            }

            wp_reset_postdata();
        }
    }
}
?>
