<?php
$items     = get_query_var('toro_ag_grid_items', []);
$img_field = get_query_var('toro_ag_grid_image_field','featured');
?>
<div class="toro-grid <?php echo esc_attr( get_query_var('toro_ag_grid_wrapper_class','') ); ?>">
    <?php foreach ($items as $item) : ?>
        <div class="toro-grid__item">
            <?php
            if (is_a($item,'WP_Term')) {
                $img_id = get_term_meta($item->term_id, $img_field, true);
                $title  = $item->name;
                $link   = get_term_link($item);
            } else {
                $img_id = ($img_field === 'featured')
                    ? get_post_thumbnail_id($item->ID)
                    : get_post_meta($item->ID, $img_field, true);
                $title  = get_the_title($item);
                $link   = get_permalink($item);
            }
            if ($link) echo "<a href='" . esc_url($link) . "'>";
            echo wp_get_attachment_image($img_id,'medium');
            if ($link) echo "</a>";
            ?>
            <?php if ($link) echo "<a href='" . esc_url($link) . "'>"; ?>
                <h4><?php echo esc_html($title); ?></h4>
            <?php if ($link) echo "</a>"; ?>
        </div>
    <?php endforeach; ?>
</div>