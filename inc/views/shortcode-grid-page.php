<?php
$items     = get_query_var('toro_ag_grid_items', []);
$img_field = get_query_var('toro_ag_grid_image_field','featured');
$title     = get_query_var('toro_ag_grid_title', '');
$columns   = get_query_var('toro_ag_grid_columns', 3); // default 3 colonne
?>

<?php if (!empty($title)) : ?>
    <!-- inc\views\shortcode-grid-page.php -->
  <h4 class="fw-bold border-bottom px-3 py-2 my-4"><?php echo esc_html($title); ?></h4>
<?php endif; ?>

<div class="toro-grid <?php echo esc_attr( get_query_var('toro_ag_grid_wrapper_class','') ); ?>" style="--toro-grid-columns: <?php echo esc_attr($columns); ?>;">
    <?php foreach ($items as $item) : ?>
        <div class="toro-grid__item"><!-- inc\views\shortcode-grid-page.php -->
            <?php
            if (is_a($item,'WP_Term')) {
                $img_id = get_term_meta($item->term_id, $img_field, true);
                $item_title  = $item->name;
                $link   = get_term_link($item);
            } else {
                $img_id = has_post_thumbnail($item->ID)
                    ? get_post_thumbnail_id($item->ID)
                    : get_post_meta($item->ID, $img_field, true);
                $item_title  = get_the_title($item);
                $link   = get_permalink($item);
            }
            if ($link) echo "<a class=\"d-block w-100\" href='" . esc_url($link) . "'>";
            echo wp_get_attachment_image($img_id, 'medium', false, ['class' => 'd-block w-100 img-fluid', 'alt' => esc_attr($item_title)]);
            if ($link) echo "</a>";
            ?>
            <?php if ($link) echo "<a href='" . esc_url($link) . "'>"; ?>
                <h4 class="pb-0"><?php echo esc_html($item_title); ?></h4>
            <?php if ($link) echo "</a>"; ?>
        </div>
    <?php endforeach; ?>
</div>