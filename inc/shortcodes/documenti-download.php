<?php
/**
 * @package ToroAG
 * @subpackage Shortcodes
 */

add_action( 'init', function() {
    add_shortcode( 'elenco_prodotti_con_dettagli', 'toroag_elenco_prodotti_con_dettagli' );
} );

if ( ! function_exists( 'toroag_elenco_prodotti_con_dettagli' ) ) {
    function toroag_elenco_prodotti_con_dettagli( $atts ) {
        $atts = shortcode_atts( ['layout'=>'grid'], $atts, 'elenco_prodotti_con_dettagli' );
        $lang = function_exists('icl_object_id')
            ? apply_filters('wpml_current_language', null)
            : 'it';

        // definisco l'ordine delle lingue
        $lang_order = [
            'inglese'   => 1,
            'spagnolo'  => 2,
            'francese'  => 3,
            'tedesco'   => 4,
            'portoghese'=> 5,
            'arabo'     => 6,
            'polacco'   => 7,
            'svedese'   => 8,
            'afrikaans' => 9,
        ];

        $terms = get_terms(['taxonomy'=>'tipo_di_prodotto','hide_empty'=>false]);
        if ( is_wp_error($terms) || empty($terms) ) {
            return '<p>'.esc_html__('Nessun termine trovato','toro-ag').'.</p>';
        }

        $terms_data = [];
        foreach ( $terms as $term ) {
            $q = new WP_Query([
                'post_type'=>'prodotto',
                'posts_per_page'=>-1,
                'orderby'=>'date',
                'order'=>'ASC',
                'tax_query'=>[[
                    'taxonomy'=>'tipo_di_prodotto',
                    'field'=>'term_id',
                    'terms'=>$term->term_id,
                ]],
            ]);

            $products = [];
            if ( $q->have_posts() ) {
                while ( $q->have_posts() ) {
                    $q->the_post();
                    $pid = get_the_ID();

                    // helper closure per estrarre schede/docs
                    $collect = function( $meta_key, $file_meta_key ) use ( $pid, $lang, $lang_order ) {
                        $items = [];
                        foreach ( (array) get_post_meta( $pid, $meta_key, false ) as $did ) {
                            $slugs = wp_get_post_terms( $did, 'lingua_aggiuntiva', ['fields'=>'slugs'] );
                            $term_slug = !empty($slugs) ? $slugs[0] : 'altre';
                            // logica di visibilità
                            $visible = ( $lang==='it' && $term_slug==='italiano' )
                                     || ( $lang!=='it' && $term_slug!=='italiano' );
                            if ( ! $visible ) {
                                continue;
                            }
                            $fid = get_post_meta( $did, $file_meta_key, true );
                            if ( $fid && ($url = wp_get_attachment_url($fid)) ) {
                                $items[] = [
                                    'title'=>get_the_title($did),
                                    'url'=>$url,
                                    'lang'=>$term_slug,
                                ];
                            }
                        }
                        // ordino per priorità lingua
                        usort( $items, function($a,$b) use($lang_order) {
                            $pA = $lang_order[$a['lang']] ?? 4;
                            $pB = $lang_order[$b['lang']] ?? 4;
                            return $pA <=> $pB;
                        });
                        return $items;
                    };

                    $schede = $collect('scheda_prodotto', 'scheda-prodotto');
                    $docs   = $collect('documento_prodotto','documento-prodotto');

                    // 2) salto i prodotti senza **alcun** documento
                    if ( empty($schede) && empty($docs) ) {
                        continue;
                    }

                    $products[] = [
                        'ID'     => $pid,
                        'title'  =>get_the_title($pid),
                        'schede' =>$schede,
                        'docs'   =>$docs,
                    ];
                    echo "\n<!-- DEBUG shortcode: slug prodotto (ID {$pid}) = ". esc_html( get_post_field( 'post_name', $pid, true ) ). " -->\n";

                }
                wp_reset_postdata();
            }

            $terms_data[] = [
                'term_name'=>$term->name,
                'products'=>$products,
            ];
        }

        // echo "\n<!-- DEBUG shortcode: \$prod['title'] = " . esc_html( $prod['title'] ) . " -->\n";

        return toroag_load_view( 'documenti-download', [
            'terms_data'=>$terms_data,
            'layout'=>$atts['layout'],
        ] );
    }
}

