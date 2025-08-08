<?php
/**
 * Template part for displaying the text of a liturgical reading.
 *
 * @package pehobr
 */

// Retrieve the text passed from the main template
$text = get_query_var( 'reading_text_content', '' );

if ( ! empty( $text ) ) :
?>
<div class="reading-text-content">
    <?php
    // We use nl2br to preserve line breaks from the Google Sheet cell.
    echo nl2br( esc_html( $text ) );
    ?>
</div>
<?php else : ?>
<p>Text tohoto čtení není momentálně k dispozici.</p>
<?php endif; ?>
