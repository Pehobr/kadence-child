<?php
/**
 * Template part for displaying additional content for a liturgical reading.
 *
 * @package pehobr
 */

// Získáme data o aktuálním čtení a výkladu
$reading_data = get_current_reading_data();
$commentary_post = $reading_data ? knihaslova_get_reading_commentary( $reading_data['sunday_id'], $reading_data['reading_type'] ) : null;
?>
<div class="sub-tabs-container">
    <ul class="sub-tabs-nav">
        <li class="active"><a href="#podcast">Podcast</a></li>
        <li><a href="#infografika">Infografika</a></li>
        <li><a href="#materialy">Materiály</a></li>
    </ul>

    <div class="sub-tab-content-wrapper">
        <div id="podcast" class="sub-tab-content active">
            <?php
            if ( $commentary_post && function_exists('get_field') ) {
                $podcast_embed = get_field('podcast', $commentary_post->ID);
                if ( !empty($podcast_embed) ) {
                    echo $podcast_embed; // Assuming embed code is saved
                } else {
                    echo '<p>Obsah pro podcast bude doplněn později.</p>';
                }
            } else {
                echo '<p>Obsah pro podcast bude doplněn později.</p>';
            }
            ?>
        </div>
        <div id="infografika" class="sub-tab-content">
             <?php
            if ( $commentary_post && function_exists('get_field') ) {
                $infographic = get_field('infografika', $commentary_post->ID);
                if ( !empty($infographic) ) {
                    echo '<img src="' . esc_url($infographic['url']) . '" alt="' . esc_attr($infographic['alt']) . '">';
                } else {
                    echo '<p>Obsah pro infografiku bude doplněn později.</p>';
                }
            } else {
                echo '<p>Obsah pro infografiku bude doplněn později.</p>';
            }
            ?>
        </div>
        <div id="materialy" class="sub-tab-content">
            <?php
            if ( $commentary_post && function_exists('get_field') ) {
                $materials = get_field('materialy', $commentary_post->ID);
                if ( !empty($materials) ) {
                    echo apply_filters('the_content', $materials);
                } else {
                    echo '<p>Obsah pro materiály bude doplněn později.</p>';
                }
            } else {
                echo '<p>Obsah pro materiály bude doplněn později.</p>';
            }
            ?>
        </div>
    </div>
</div>
