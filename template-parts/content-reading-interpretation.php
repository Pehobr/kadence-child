<?php
/**
 * Template part for displaying the interpretation of a liturgical reading.
 *
 * @package pehobr
 */

// Získáme data o aktuálním čtení a výkladu
$reading_data = get_current_reading_data();
$commentary_post = $reading_data ? knihaslova_get_reading_commentary( $reading_data['sunday_id'], $reading_data['reading_type'] ) : null;
?>
<div class="sub-tabs-container">
    <ul class="sub-tabs-nav">
        <li class="active"><a href="#exegeze">Exegeze</a></li>
        <li><a href="#duchovni-vyklad">Duchovní výklad</a></li>
    </ul>

    <div class="sub-tab-content-wrapper">
        <div id="exegeze" class="sub-tab-content active">
            <?php
            if ( $commentary_post && function_exists('get_field') ) {
                $content = get_field('exegeze', $commentary_post->ID);
                if ( !empty($content) ) {
                    echo apply_filters('the_content', $content);
                } else {
                    echo '<p>Obsah pro exegezi bude doplněn později.</p>';
                }
            } else {
                echo '<p>Obsah pro exegezi bude doplněn později.</p>';
            }
            ?>
        </div>
        <div id="duchovni-vyklad" class="sub-tab-content">
            <?php
            if ( $commentary_post && function_exists('get_field') ) {
                $content = get_field('duchovni_vyklad', $commentary_post->ID);
                if ( !empty($content) ) {
                    echo apply_filters('the_content', $content);
                } else {
                    echo '<p>Obsah pro duchovní výklad bude doplněn později.</p>';
                }
            } else {
                echo '<p>Obsah pro duchovní výklad bude doplněn později.</p>';
            }
            ?>
        </div>
    </div>
</div>
