<?php
/**
 * Converte i simboli ™ e ® in superscript,
 * aggiungendo classi distinte quando <sup> esiste già o wrappa una volta se non esiste.
 */
function toro_ag_trademarks_to_superscript( $text ) {
    if ( empty( $text ) ) {
        return $text;
    }

    // 1) Rimuove eventuali tag “HTML-escaped”:
    //    &lt;sup&gt;™&lt;/sup&gt; oppure &lt;sup&gt;®&lt;/sup&gt;
    $text = preg_replace(
        '#(?:&lt;sup&gt;)+(™|®)(?:&lt;/sup&gt;)+#u',
        '$1',
        $text
    );

    // 2) Aggiunge la classe se <sup>™</sup> o <sup>®</sup> esistono già (senza classe)
    $text = preg_replace_callback(
        '#<sup>(™)</sup>#u',
        function( $matches ) {
            return '<sup class="tm">' . $matches[1] . '</sup>';  
        },
        $text
    );
    $text = preg_replace_callback(
        '#<sup>(®)</sup>#u',
        function( $matches ) {
            return '<sup class="r">' . $matches[1] . '</sup>';  
        },
        $text
    );

    // 3) Rimuove eventuali <sup class="tm">™</sup> o <sup class="r">®</sup> nidificati
    $text = preg_replace(
        '#<sup class="tm">(™)</sup>#u',
        '$1',
        $text
    );
    $text = preg_replace(
        '#<sup class="r">(®)</sup>#u',
        '$1',
        $text
    );

    // 4) Wrappa una sola volta ciascun simbolo che non è stato wrappato
    $text = str_replace( '™', '<sup class="tm">™</sup>', $text );
    $text = str_replace( '®', '<sup class="r">®</sup>', $text );

    return $text;
}
