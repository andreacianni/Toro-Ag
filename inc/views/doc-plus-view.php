<?php
/**
 * Template: doc-plus-view.php
 * Riceve i dati in $doc_plus_data e genera l'HTML frontend con Bootstrap
 */
$docs = get_query_var('doc_plus_data');
if ( empty( $docs ) || ! is_array( $docs ) ) {
    return;
}
?>
<div class="row doc-plus-list">
    <?php foreach ( $docs as $doc ) : ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if ( ! empty( $doc['cover_url'] ) ) : ?>
                    <img src="<?php echo esc_url( $doc['cover_url'] ); ?>"
                         class="card-img-top"
                         alt="<?php echo esc_attr( $doc['title'] ); ?>">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html( $doc['title'] ); ?></h5>
                    <?php if ( ! empty( $doc['attachments'] ) ) : ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ( $doc['attachments'] as $att ) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="<?php echo esc_url( $att['url'] ); ?>"
                                       target="_blank" rel="noopener">
                                        <?php echo esc_html( $att['title'] ); ?>
                                    </a>
                                    <span class="ml-auto">
                                        <?php echo $att['flag']; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
