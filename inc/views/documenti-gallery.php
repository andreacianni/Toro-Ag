<?php
/**
 * View: documenti-gallery.php
 * Versione tema: toro-ag-template V0.9.5
 */
?>
<?php foreach ($categorie as $categoria): ?>
    <?php
        $args = [
            'post_type' => 'documento_agente',
            'posts_per_page' => -1,
            'tax_query' => [[
                'taxonomy' => 'categoria_documento',
                'field' => 'term_id',
                'terms' => $categoria->term_id,
            ]],
        ];
        $query = new WP_Query($args);
    ?>
    <?php if ($query->have_posts()): ?>
        <h4 class="fw-semibold mb-3 border-bottom"><?php echo esc_html($categoria->name); ?></h4>
        <div class="row g-3 mb-5">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php
                    $titolo = get_the_title();
                    $data_doc = get_post_meta(get_the_ID(), 'data_documento', true);
                    $file_id = get_post_meta(get_the_ID(), 'file_pdf', true);
                    $link_pdf = wp_get_attachment_url($file_id);
                    $thumb_url = wp_get_attachment_image_url($file_id, 'medium');
                    $secure_link = add_query_arg('secure_download', get_the_ID(), home_url());

                ?>
                <div class="col-6 col-md-3">
                    <a href="<?php echo esc_url($secure_link); ?>" target="_blank" class="text-decoration-none text-reset h-100 d-block">
                        <div class="border rounded shadow-sm h-100 d-flex flex-column overflow-hidden">
                            <?php if ($thumb_url): ?>
                                <img src="<?php echo esc_url($thumb_url); ?>" class="img-fluid rounded-top" alt="Anteprima PDF">
                            <?php else: ?>
                                <div class="pt-4 pb-2 text-center">
                                    <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
                                </div>
                            <?php endif; ?>
                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1 text-center">
                                <div>
                                    <div class="fw-medium mb-1"><?php echo esc_html($titolo); ?></div>
                                    <small class="text-muted"><?php echo esc_html(date('d/m/Y', strtotime($data_doc))); ?></small>
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="btn btn-sm btn-outline-primary">Apri PDF</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>
    <?php // NOTA: Sezioni vuote non vengono più mostrate - Fix #3 implementato ?>
<?php endforeach; ?>