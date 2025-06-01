<?php
/**
 * Converte i simboli ™ e ® in superscript.
 * Prima rimuove ogni possibile <sup>…</sup> già esistente (anche se è "escapato"),
 * poi wrappa una sola volta ciascun simbolo in <sup>…</sup> con classi distinte.
 */
function toro_ag_trademarks_to_superscript( $text ) {
    if ( empty( $text ) ) {
        return $text;
    }

    // 1) Rimuove eventuali tag “HTML-escaped”:
    //    &lt;sup&gt;™&lt;/sup&gt; oppure &lt;sup&gt;®&lt;/sup&gt;
    $text = preg_replace(
        '#&lt;sup&gt;(™|®)&lt;/sup&gt;#u', 
        '$1', 
        $text
    );

    // 2) Rimuove eventuali tag <sup>™</sup> o <sup>®</sup> già "veri":
    $text = preg_replace(
        '#<sup>(™|®)</sup>#u', 
        '$1', 
        $text
    );

    // 3) Ora che non ci sono più sup-wrap duplicati (neanche escapati),
    //    wrappa una sola volta ciascun simbolo con classi distinte:
    //    – ™ diventa <sup class="tm">™</sup>
    //    – ® diventa <sup class="r">®</sup>
    $text = str_replace( '™', '<sup class="tm">™</sup>', $text );
    $text = str_replace( '®', '<sup class="r">®</sup>', $text );

    return $text;
}
