<?php
/**
 * Template Name: Katalog příběhů
 */

get_header(); ?>

<div id="primary" class="content-area page-katalog">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <header class="page-header">
                <h1 class="page-title" style="text-align: center;"><?php the_title(); ?></h1>
            </header>

            <div class="view-switcher">
                <button class="nav-tab active" data-target="name-view">Podle názvů</button>
                <button class="nav-tab" data-target="citation-view">Podle citací</button>
            </div>

            <div id="name-view" class="tab-content active">
                <div class="katalog-grid">
                    <?php
                    $args = array(
                        'post_type' => 'evangelijni_pribeh',
                        'posts_per_page' => -1,
                        'orderby' => 'title',
                        'order' => 'ASC',
                    );
                    $pribehy = new WP_Query($args);
                    if ($pribehy->have_posts()) {
                        while ($pribehy->have_posts()) {
                            $pribehy->the_post();
                            echo '<a href="' . get_permalink() . '" class="katalog-button">' . get_the_title() . '</a>';
                        }
                    }
                    wp_reset_postdata();
                    ?>
                </div>
            </div>

            <div id="citation-view" class="tab-content">
                <div class="evangelist-switcher">
                    <?php
                    $evangeliste_slugs = array('matous', 'marek', 'lukas', 'jan');
                    
                    $display_names = [
                        'matous' => 'Matouš',
                        'marek'  => 'Marek',
                        'lukas'  => 'Lukáš',
                        'jan'    => 'Jan'
                    ];

                    $short_names = [
                        'matous' => 'Mt',
                        'marek'  => 'Mk',
                        'lukas'  => 'Lk',
                        'jan'    => 'Jan'
                    ];

                    foreach ($evangeliste_slugs as $index => $slug) {
                        $active_class = ($index === 0) ? 'active' : '';
                        echo '<button class="nav-tab ' . $active_class . '" data-evangelist="' . $slug . '">
                                <span class="fullname">' . esc_html($display_names[$slug]) . '</span>
                                <span class="shortname">' . esc_html($short_names[$slug]) . '</span>
                              </button>';
                    }
                    ?>
                </div>

                <div class="citations-content">
                    <?php
                    foreach ($evangeliste_slugs as $index => $slug) {
                        $active_class = ($index === 0) ? 'active' : '';
                        echo '<div id="evangelist-' . $slug . '" class="evangelist-citation-content ' . $active_class . '">';
                        echo '<div class="katalog-grid">';

                        // 1. Načteme všechny příběhy, které mají citaci pro daného evangelistu
                        $args = array(
                            'post_type'      => 'evangelijni_pribeh',
                            'posts_per_page' => -1,
                            'meta_query'     => array(
                                array(
                                    'key'     => $slug . '_citace',
                                    'value'   => '',
                                    'compare' => '!=',
                                ),
                            ),
                        );
                        $pribehy_query = new WP_Query($args);

                        // 2. Vložíme je do pole, které následně seřadíme
                        $stories_to_sort = [];
                        if ($pribehy_query->have_posts()) {
                            while ($pribehy_query->have_posts()) {
                                $pribehy_query->the_post();
                                $citation = get_post_meta(get_the_ID(), $slug . '_citace', true);
                                if (!empty($citation)) {
                                    $stories_to_sort[] = [
                                        'permalink' => get_permalink(),
                                        'citation'  => $citation,
                                    ];
                                }
                            }
                        }
                        wp_reset_postdata();

                        // 3. Seřadíme pole pomocí naší pomocné funkce
                        if (!empty($stories_to_sort) && function_exists('knihaslova_get_citation_sort_key')) {
                            usort($stories_to_sort, function($a, $b) {
                                $sort_a = knihaslova_get_citation_sort_key($a['citation']);
                                $sort_b = knihaslova_get_citation_sort_key($b['citation']);
                                return $sort_a <=> $sort_b;
                            });
                        }

                        // 4. Zobrazíme správně seřazená tlačítka
                        if (!empty($stories_to_sort)) {
                            foreach ($stories_to_sort as $story) {
                                echo '<a href="' . esc_url($story['permalink']) . '" class="katalog-button">' . esc_html($story['citation']) . '</a>';
                            }
                        }
                        
                        echo '</div></div>';
                    }
                    ?>
                </div>
            </div>
        </main>
        
        <?php get_sidebar(); ?>

    </div>
</div>

<?php
get_footer();