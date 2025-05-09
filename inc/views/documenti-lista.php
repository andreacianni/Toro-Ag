<?php
/**
 * View: documenti-lista.php
 * Versione tema: toro-ag-template V0.9.5
 */
?>
<?php foreach ($categorie as $categoria): ?>
    <h4><?php echo esc_html($categoria->name); ?></h4>
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
        <div class="mx-4 mt-2  mb-5">
            <div class="row border-bottom py-2 d-none d-md-flex">
                <div class="col-md-8">Documentazione</div>
                <div class="col-md-2 text-center">data</div>
                <div class="col-md-2 text-center">download</div>
            </div>
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php
                    $titolo = get_the_title();
                    $data_doc = get_post_meta(get_the_ID(), 'data_documento', true);
                    $file_id = get_post_meta(get_the_ID(), 'file_pdf', true);
                    $link_pdf = wp_get_attachment_url($file_id);
                ?>
                <div class="row border-bottom py-2 align-items-center">
                    <div class="col-8 col-md-8 fw-medium"><?php echo esc_html($titolo); ?></div>
                    <div class="col-2 col-md-2 text-center"><?php echo esc_html(date('d/m/Y', strtotime($data_doc))); ?></div>
                    <div class="col-2 col-md-2 text-center">
                        <?php if ($link_pdf): ?>
                            <a href="<?php echo esc_url($link_pdf); ?>" target="_blank">
                                <i class="bi bi-file-earmark-pdf fs-4 text-danger"></i>
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    <?php else: ?>
        <p>La sezione non contiene documenti.</p>
    <?php endif; ?>
<?php endforeach; ?>