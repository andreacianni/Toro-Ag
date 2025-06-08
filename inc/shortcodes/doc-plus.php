<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID opzionale
 * Recupera sempre tutti i doc_plus collegati dalla pagina corrente,
 * poi se 'ids' è passato filtra quali elementi mostrare.
 * Aggiunti debug visibili per tracciare il flusso tra i vari if/else.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Parsing attributi
    $atts = shortcode_atts( ['ids'=>''], $atts, 'doc_plus' );
    $filter_ids = [];
    if( !empty($atts['ids']) ){
        foreach(preg_split('/\s*,\s*/',$atts['ids']) as $v){ if($i=intval($v)) $filter_ids[]=$i; }
    }
    
    ob_start();

    // 2) Lingua
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language',null);
    $default_lang = apply_filters('wpml_default_language',null);

    // 3) Relation raw meta
    $page_id = get_the_ID();
    echo '<small class="d-block text-center text-warning">doc_plus_debug: STEP relation raw meta from page_id='.$page_id.'</small>';
    $raw = get_post_meta($page_id,'doc_plus_inpage',false);
    $related=[];
    foreach($raw as $e){
        if(is_array($e)&&isset($e['ID'])){
            $related[]= ['ID'=>intval($e['ID'])];
        }elseif(is_string($e)){
            $un=@unserialize($e);
            if(is_array($un)){
                foreach($un as $v){ $related[]= ['ID'=>intval($v)]; }
            }elseif(is_numeric($e)){
                $related[]= ['ID'=>intval($e)];
            }
        }
    }
    echo '<small class="d-block text-center text-warning">doc_plus_debug: related IDs='.implode(',',array_map(function($r){return$r['ID'];},$related)).'</small>';

    if(empty($related)){
        echo '<small class="d-block text-center text-danger">doc_plus_debug: no doc_plus linked</small>';
        return ob_get_clean();
    }

    // 4) Header
    echo '<small class="d-block text-center text-info">doc_plus_debug: lang='.$current_lang.
         (empty($filter_ids)?'':', filter IDs='.implode(',',$filter_ids)).'</small>';
    
    // 5) Count
    $total=count($related); $shown=0;
    foreach($related as $r){ if(empty($filter_ids)||in_array($r['ID'],$filter_ids,true)) $shown++; }
    echo '<small class="d-block text-center text-muted">trovati '.$total.' doc_plus'.(
         empty($filter_ids)?'':', mostrati '.$shown).'</small>';

    // 6) Loop doc_plus
    foreach($related as $r){
        $doc_id=intval($r['ID']);
        if(!empty($filter_ids)&&!in_array($doc_id,$filter_ids,true)){
            echo '<small class="d-block text-center text-secondary">doc_plus_debug: skip doc_id='.$doc_id.' (filtered)</small>';
            continue;
        }
        echo '<small class="d-block text-success">doc_plus_debug: processing doc_id='.$doc_id.'</small>';
        // Load Pod
        $pod=pods('doc_plus',$doc_id,['lang'=>$current_lang]);
        if(!$pod->exists()){
            echo '<small class="d-block text-warning">doc_plus_debug: fallback pod for doc_id='.$doc_id.'</small>';
            $fb=apply_filters('wpml_object_id',$doc_id,'doc_plus',true,$default_lang)?:$doc_id;
            $pod=pods('doc_plus',$fb,['lang'=>$default_lang]);
        }
        // Title & Cover
        echo '<small class="d-block">DOC ID='.$pod->ID().' titolo="'.esc_html(get_the_title($pod->ID())).'"</small>';
        $cover_id=$pod->field('doc_plus_cover.ID');
        echo '<small class="d-block">cover_id='.$cover_id.'</small>';

        // 7) ALLEGATI debug
        $attachments=(array)$pod->field('doc_plus_allegati');
        echo '<small class="d-block text-primary">doc_plus_debug: field attachments count='.count($attachments).'</small>';
        if(empty($attachments)){
            echo '<small class="d-block text-warning">doc_plus_debug: fallback raw attachments for doc_id='.$pod->ID().'</small>';
            foreach(get_post_meta($pod->ID(),'doc_plus_allegati',false) as $e2){
                if(is_array($e2)&&isset($e2['ID'])){
                    $attachments[]=['ID'=>intval($e2['ID'])];
                }elseif(is_string($e2)){
                    $u2=@unserialize($e2);
                    if(is_array($u2))foreach($u2 as $v2)$attachments[]=['ID'=>intval($v2)];
                    elseif(is_numeric($e2))$attachments[]=['ID'=>intval($e2)];
                }
            }
            echo '<small class="d-block text-primary">doc_plus_debug: raw attachments count='.count($attachments).'</small>';
        }
        // Show attachments
        foreach($attachments as $att){
            $aid=intval($att['ID']);
            echo '<small class="d-block text-info">doc_plus_debug: processing attachment ID='.$aid.'</small>';
            $pp=pods('documenti_prodotto',$aid,['lang'=>$current_lang]);
            if(!$pp->exists()){
                echo '<small class="d-block text-warning">doc_plus_debug: fallback pp for pdf_id='.$aid.'</small>';
                $fbp=apply_filters('wpml_object_id',$aid,'documenti_prodotto',true,$default_lang)?:$aid;
                $pp=pods('documenti_prodotto',$fbp,['lang'=>$default_lang]);
            }
            $title=get_the_title($pp->ID());
            $fid=$pp->field('documento-prodotto.ID');
            $url=$fid?wp_get_attachment_url($fid):'';
            $langs=$pp->field('lingua_aggiuntiva');
            $lt=!empty($langs)?$langs[0]:['slug'=>'n.d.','name'=>'n.d.'];
            echo '<small class="d-block">PDF ID='.$pp->ID().' tit='.$title.' url='.$url.' lingua='.$lt['slug'].':'.$lt['name'].'</small>';
        }
    }

    // 8) Close
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus','doc_plus_debug_shortcode');
