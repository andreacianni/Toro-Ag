<?php
$items     = get_query_var('toro_ag_grid_items', []);
$img_field = get_query_var('toro_ag_grid_image_field','featured');
$title     = get_query_var('toro_ag_grid_title', '');
$columns   = get_query_var('toro_ag_grid_columns', 3); // default 3 colonne

// Calcola le classi Bootstrap per le colonne
$bootstrap_cols = array(
    1 => 'col-12',
    2 => 'col-md-6',
    3 => 'col-lg-4',
    4 => 'col-lg-3',
    5 => 'col-lg-2', // 5 colonne: un po' stretto ma funziona
    6 => 'col-lg-2'
);
$col_class = isset($bootstrap_cols[$columns]) ? $bootstrap_cols[$columns] : 'col-lg-4';
?>

<?php if (!empty($title)) : ?>
  <h5 class="text-bg-dark px-3 py-2 my-4 rounded-2"><?php echo esc_html($title); ?></h5>
<?php endif; ?>

<div class="row g-4 <?php echo esc_attr( get_query_var('toro_ag_grid_wrapper_class','') ); ?>">
    <?php foreach ($items as $item) : ?>
        <div class="<?php echo esc_attr($col_class); ?>">
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