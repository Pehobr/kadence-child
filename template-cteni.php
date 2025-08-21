<?php
/**
 * Template Name: Detail Čtení
 *
 * This is the template that displays the detail of a liturgical reading.
 * It uses a tabbed interface to show different aspects of the reading.
 *
 * @package pehobr
 */

// Get the reading data using the controller function
$reading_data = get_current_reading_data();

// Redirect to 404 if no data is found for the given parameters
if ( ! $reading_data ) {
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 );
    exit();
}

// Získáme data o komentáři, abychom je mohli použít pro více záložek
$commentary_post = $reading_data ? knihaslova_get_reading_commentary( $reading_data['sunday_id'], $reading_data['reading_type'] ) : null;

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="container reading-detail-container">
            <div class="reading-header">
                <div class="breadcrumbs">
                    <a href="<?php echo esc_url( home_url( '/liturgicky-rok/' ) ); ?>">Liturgický rok</a> &raquo; <?php echo esc_html( $reading_data['sunday_name'] ); ?>
                </div>
                <h1><?php echo esc_html( $reading_data['title'] ); ?></h1>
                <p class="citation"><?php echo esc_html( $reading_data['citation'] ); ?></p>
            </div>

            <div class="tabs-container">
                <ul class="tabs-nav">
                    <li class="active"><a href="#text">Text</a></li>
                    <li><a href="#vyklad">Výklad</a></li>
                    <li><a href="#podcast">Podcast</a></li>
                    <li><a href="#materialy">Materiály</a></li>
                </ul>

                <div class="tab-content-wrapper">
                    <div id="text" class="tab-content active">
                        <?php
                        set_query_var( 'reading_text_content', $reading_data['text'] );
                        get_template_part( 'template-parts/content', 'reading-text' );
                        ?>
                    </div>
                    <div id="vyklad" class="tab-content">
                        <?php get_template_part( 'template-parts/content', 'reading-interpretation' ); ?>
                    </div>
                    <div id="podcast" class="tab-content">
                        <?php
                        if ( $commentary_post && function_exists('get_field') ) {
                            $podcast_content = get_field('podcast', $commentary_post->ID);
                            if ( !empty($podcast_content) ) {
                                $trimmed_content = trim($podcast_content);
                                if ( filter_var($trimmed_content, FILTER_VALIDATE_URL) ) {
                                    echo wp_audio_shortcode( array('src' => esc_url($trimmed_content)) );
                                } else {
                                    echo $podcast_content;
                                }
                            } else {
                                echo '<p>Obsah pro podcast bude doplněn později.</p>';
                            }
                        } else {
                            echo '<p>Obsah pro podcast bude doplněn později.</p>';
                        }
                        ?>
                    </div>
                    <div id="materialy" class="tab-content">
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
        </div>
    </main>
</div>

<?php
get_footer();
