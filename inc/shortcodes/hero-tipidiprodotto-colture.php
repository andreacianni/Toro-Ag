<?php
// inc/shortcodes/hero-tipidiprodotto-colture.php

add_shortcode( 'hero_tipo_prodotto_e_coltura', function() {
    // Verifica tassonomia
    if ( ! is_tax( [ 'tipo_di_prodotto', 'coltura' ] ) ) {
        return '';
    }
    $term = get_queried_object();
    if ( ! $term || empty( $term->term_id ) ) {
        return '';
    }

    // Determina campo in base alla tassonomia
    $taxonomy = $term->taxonomy;
    $field    = ( $taxonomy === 'coltura' ) ? 'pod_hero_coltura' : 'tipo-hero';

    // WPML: mappa all'ID originale in italiano se necessario
    $default_lang = 'it';
    $use_term_id  = $term->term_id;
    $lang = function_exists('apply_filters') ? apply_filters('wpml_current_language', null) : null;
    if ( $lang && $lang !== $default_lang && function_exists('apply_filters') ) {
        $orig_id = apply_filters('wpml_object_id', $term->term_id, $taxonomy, true, $default_lang);
        if ( $orig_id ) {
            $use_term_id = $orig_id;
        }
    }

    // Tentativo pods_image_url
    $url = function_exists( 'pods_image_url' )
        ? pods_image_url( $taxonomy, $use_term_id, $field, 'full' )
        : '';

    // Fallback: recupera meta termine e URL
    if ( empty( $url ) ) {
        $meta_single = get_term_meta( $use_term_id, $field, true );
        if ( is_numeric( $meta_single ) ) {
            $id = intval( $meta_single );
        } elseif ( is_array( $meta_single ) && is_numeric( $meta_single[0] ) ) {
            $id = intval( $meta_single[0] );
        } else {
            return '';
        }
        $url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
    }

    // Se nessuna URL, non stampare nulla
    if ( empty( $url ) ) {
        return '';
    }

    // Stampa sezione hero con altezza modificata
    return sprintf(
        '<section class="tipo-prodotto-hero" style="'
            . 'background-image: url(%1$s);'
            . 'background-size: cover;'
            . 'background-position: center;'
            . 'width: 100%%;'
            . 'height: 30vh;'
            . 'margin: 0;"'
        . '></section>',
        esc_url( $url )
    );
} );
