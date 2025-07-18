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

        // 1) Determino subito la lingua corrente
        $lang = function_exists( 'icl_object_id' )
        ? apply_filters( 'wpml_current_language', null )
        : 'it';

        // se la lingua NON è italiana, enqueue + localize
        if ( $lang !== 'it' ) {
            wp_enqueue_script(
                'toroag-documenti-filter',
                get_stylesheet_directory_uri() . '/assets/js/documenti-filter.js',
                [],
                '1.0',
                true
            );
            wp_localize_script(
                'toroag-documenti-filter',
                'toroagFilterConfig',
                [
                    'currentLang' => $lang,
                ]
            );
        }

        $atts = shortcode_atts( ['layout'=>'grid'], $atts, 'elenco_prodotti_con_dettagli' );
        $lang = function_exists('icl_object_id')
            ? apply_filters('wpml_current_language', null)
            : 'it';

        // definisco l'ordine delle lingue
        $lang_order = function_exists( 'toroag_get_language_order' )
            ? toroag_get_language_order()
            : [];

        $terms = get_terms(['taxonomy'=>'tipo_di_prodotto','hide_empty'=>false]);
        if ( is_wp_error($terms) || empty($terms) ) {
            return '<p>'.esc_html__('Nessun termine trovato','toro-ag').'.</p>';
        }

        $terms_data = [];

        // SEZIONE 1: PRODOTTI (logica esistente)
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
                }
                wp_reset_postdata();
            }

            $terms_data[] = [
                'term_name'=>$term->name,
                'products'=>$products,
            ];
        }

        // SEZIONE 2: ALTRA DOCUMENTAZIONE
        
        // Helper per raccogliere documenti dalle pagine
        $collect_page_docs = function( $page_id, $meta_key, $file_meta_key ) use ( $lang, $lang_order ) {
            $items = [];
            $docs_ids = get_post_meta( $page_id, $meta_key, false );
            
            foreach ( (array) $docs_ids as $did ) {
                $doc_id = is_array($did) && isset($did['ID']) ? $did['ID'] : (is_object($did) && isset($did->ID) ? $did->ID : intval($did));
                if ( ! $doc_id ) continue;
                
                // WPML: traduzione del documento
                if ( function_exists('icl_object_id') ) {
                    $translated_doc_id = apply_filters('wpml_object_id', $doc_id, get_post_type($doc_id), false, $lang);
                    if ( $translated_doc_id ) {
                        $doc_id = $translated_doc_id;
                    }
                }
                
                $slugs = wp_get_post_terms( $doc_id, 'lingua_aggiuntiva', ['fields'=>'slugs'] );
                $term_slug = !empty($slugs) ? $slugs[0] : 'altre';
                
                // logica di visibilità
                $visible = ( $lang==='it' && $term_slug==='italiano' )
                         || ( $lang!=='it' && $term_slug!=='italiano' );
                if ( ! $visible ) {
                    continue;
                }
                
                $fid = get_post_meta( $doc_id, $file_meta_key, true );
                if ( $fid && ($url = wp_get_attachment_url($fid)) ) {
                    $items[] = [
                        'title'=>get_the_title($doc_id),
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

        // Helper per raccogliere doc_plus
        $collect_doc_plus = function( $page_id ) use ( $lang, $lang_order ) {
            $items = [];
            $doc_plus_ids = get_post_meta( $page_id, 'doc_plus_inpage', false );
            
            foreach ( (array) $doc_plus_ids as $doc_plus_data ) {
                $doc_plus_id = is_array($doc_plus_data) && isset($doc_plus_data['ID']) ? 
                               $doc_plus_data['ID'] : 
                               (is_object($doc_plus_data) && isset($doc_plus_data->ID) ? 
                                $doc_plus_data->ID : intval($doc_plus_data));
                
                if ( ! $doc_plus_id ) continue;
                
                // WPML: traduzione del doc_plus
                if ( function_exists('icl_object_id') ) {
                    $translated_id = apply_filters('wpml_object_id', $doc_plus_id, 'doc_plus', false, $lang);
                    if ( $translated_id ) {
                        $doc_plus_id = $translated_id;
                    }
                }
                
                // Recupera gli allegati del doc_plus
                $allegati_ids = get_post_meta( $doc_plus_id, 'doc_plus_allegati', false );
                
                foreach ( (array) $allegati_ids as $allegato_data ) {
                    $allegato_id = is_array($allegato_data) && isset($allegato_data['ID']) ? 
                                   $allegato_data['ID'] : 
                                   (is_object($allegato_data) && isset($allegato_data->ID) ? 
                                    $allegato_data->ID : intval($allegato_data));
                    
                    if ( ! $allegato_id ) continue;
                    
                    // WPML: traduzione dell'allegato
                    if ( function_exists('icl_object_id') ) {
                        $translated_allegato = apply_filters('wpml_object_id', $allegato_id, 'documenti_prodotto', false, $lang);
                        if ( $translated_allegato ) {
                            $allegato_id = $translated_allegato;
                        }
                    }
                    
                    $slugs = wp_get_post_terms( $allegato_id, 'lingua_aggiuntiva', ['fields'=>'slugs'] );
                    $term_slug = !empty($slugs) ? $slugs[0] : 'altre';
                    
                    // logica di visibilità
                    $visible = ( $lang==='it' && $term_slug==='italiano' )
                             || ( $lang!=='it' && $term_slug!=='italiano' );
                    if ( ! $visible ) {
                        continue;
                    }
                    
                    $fid = get_post_meta( $allegato_id, 'documento-prodotto', true );
                    if ( $fid && ($url = wp_get_attachment_url($fid)) ) {
                        $items[] = [
                            'title'=>get_the_title($allegato_id),
                            'url'=>$url,
                            'lang'=>$term_slug,
                        ];
                    }
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

        // Recupera tutte le pagine pubblicate (escludendo ID 865)
        $pages_query = new WP_Query([
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'post__not_in' => [865], // Escludi area-agenti
            'orderby' => 'title',
            'order' => 'ASC'
        ]);

        if ( $pages_query->have_posts() ) {
            while ( $pages_query->have_posts() ) {
                $pages_query->the_post();
                $page_id = get_the_ID();
                
                // WPML: usa la versione tradotta della pagina
                if ( function_exists('icl_object_id') ) {
                    $translated_page_id = apply_filters('wpml_object_id', $page_id, 'page', false, $lang);
                    if ( $translated_page_id ) {
                        $page_id = $translated_page_id;
                    }
                }
                
                // Raccogli tutti i documenti della pagina
                $schede_pagina = $collect_page_docs( $page_id, 'schede_pagina', 'scheda-prodotto' );
                $documenti_pagina = $collect_page_docs( $page_id, 'documenti_pagina', 'documento-prodotto' );
                $doc_plus_pagina = $collect_doc_plus( $page_id );
                
                // Unisci tutti i documenti
                $all_docs = array_merge( $schede_pagina, $documenti_pagina, $doc_plus_pagina );
                
                // Solo se ci sono documenti, aggiungi la pagina
                if ( ! empty( $all_docs ) ) {
                    // Titolo speciale per la home
                    $page_title = ( is_front_page() && $page_id == get_option('page_on_front') ) ? 
                                  'Cataloghi' : get_the_title( $page_id );
                    
                    $terms_data[] = [
                        'term_name' => $page_title,
                        'products' => [[
                            'ID' => $page_id,
                            'title' => $page_title,
                            'schede' => [],
                            'docs' => $all_docs,
                        ]],
                    ];
                }
            }
            wp_reset_postdata();
        }

        // SCHEDA APPLICAZIONI (brochure colture)
        $colture_terms = get_terms(['taxonomy'=>'coltura','hide_empty'=>false]);
        $brochure_applicazioni = [];
        
        if ( ! is_wp_error($colture_terms) && ! empty($colture_terms) ) {
            foreach ( $colture_terms as $coltura_term ) {
                // WPML: traduzione termine
                if ( function_exists('icl_object_id') ) {
                    $translated_term_id = apply_filters('wpml_object_id', $coltura_term->term_id, 'coltura', false, $lang);
                    if ( $translated_term_id ) {
                        $coltura_term_id = $translated_term_id;
                    } else {
                        $coltura_term_id = $coltura_term->term_id;
                    }
                } else {
                    $coltura_term_id = $coltura_term->term_id;
                }
                
                // Recupera brochure associate alla coltura
                $brochure_ids = get_term_meta( $coltura_term_id, 'brochure_coltura', false );
                
                foreach ( (array) $brochure_ids as $brochure_data ) {
                    $brochure_id = is_array($brochure_data) && isset($brochure_data['ID']) ? 
                                   $brochure_data['ID'] : 
                                   (is_object($brochure_data) && isset($brochure_data->ID) ? 
                                    $brochure_data->ID : intval($brochure_data));
                    
                    if ( ! $brochure_id ) continue;
                    
                    // WPML: traduzione brochure
                    if ( function_exists('icl_object_id') ) {
                        $translated_brochure = apply_filters('wpml_object_id', $brochure_id, 'brochure_coltura', false, $lang);
                        if ( $translated_brochure ) {
                            $brochure_id = $translated_brochure;
                        }
                    }
                    
                    $slugs = wp_get_post_terms( $brochure_id, 'lingua_aggiuntiva', ['fields'=>'slugs'] );
                    $term_slug = !empty($slugs) ? $slugs[0] : 'altre';
                    
                    // logica di visibilità
                    $visible = ( $lang==='it' && $term_slug==='italiano' )
                             || ( $lang!=='it' && $term_slug!=='italiano' );
                    if ( ! $visible ) {
                        continue;
                    }
                    
                    $fid = get_post_meta( $brochure_id, 'brochure-file', true );
                    if ( $fid && ($url = wp_get_attachment_url($fid)) ) {
                        $brochure_applicazioni[] = [
                            'title'=>get_the_title($brochure_id),
                            'url'=>$url,
                            'lang'=>$term_slug,
                        ];
                    }
                }
            }
        }
        
        // Ordina le brochure per priorità lingua
        usort( $brochure_applicazioni, function($a,$b) use($lang_order) {
            $pA = $lang_order[$a['lang']] ?? 4;
            $pB = $lang_order[$b['lang']] ?? 4;
            return $pA <=> $pB;
        });
        
        // Aggiungi scheda Applicazioni solo se ci sono brochure
        if ( ! empty( $brochure_applicazioni ) ) {
            $terms_data[] = [
                'term_name' => 'Applicazioni',
                'products' => [[
                    'ID' => 0,
                    'title' => 'Applicazioni',
                    'schede' => [],
                    'docs' => $brochure_applicazioni,
                ]],
            ];
        }

        return toroag_load_view( 'documenti-download', [
            'terms_data'    =>$terms_data,
            'layout'        =>$atts['layout'],
            'lang'          =>$lang,
            'lang_order'    =>$lang_order,
        ] );
    }
}