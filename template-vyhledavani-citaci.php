<?php
/**
 * Template Name: Vyhledávání citací
 * Template Post Type: page
 */

get_header();

// Zpracování formuláře
$selected_evangelist = isset($_GET['evangelista']) ? sanitize_text_field($_GET['evangelista']) : '';
$selected_chapter = isset($_GET['kapitola']) ? intval($_GET['kapitola']) : '';
$search_results = [];

if ($selected_evangelist && $selected_chapter) {
    // Název meta klíče pro citaci (např. 'matous_citace')
    $meta_key = strtolower($selected_evangelist) . '_citace';

    // Argumenty pro WP_Query
    $args = array(
        'post_type' => 'evangelijni_pribeh',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => $meta_key,
                // Hledáme shodu na začátku textu, např. "Mt 3," nebo "Lk 12,"
                // Mezera po názvu evangelisty je důležitá, aby se "1" nenašlo v "12"
                'value' => $selected_chapter . ',',
                'compare' => 'LIKE',
            ),
        ),
    );
    
    // Provedení dotazu
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $search_results[] = [
                'title' => get_post_meta(get_the_ID(), $meta_key, true),
                'permalink' => get_permalink(),
            ];
        }
    }
    wp_reset_postdata();
}

// Pole pro zobrazení jmen s diakritikou
$display_names = [
    'Matous' => 'Matouš',
    'Marek'  => 'Marek',
    'Lukas'  => 'Lukáš',
    'Jan'    => 'Jan'
];
?>

<div id="primary" class="content-area page-citation-search">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <header class="page-header">
                <h1 class="page-title" style="text-align: center;"><?php the_title(); ?></h1>
            </header>

            <div class="citation-search-form-container">
                <form role="search" method="get" class="citation-search-form" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="evangelista">Vyberte evangelistu:</label>
                            <select name="evangelista" id="evangelista" class="search-select">
                                <option value="">-- Evangelista --</option>
                                <?php foreach ($display_names as $key => $name): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_evangelist, $key); ?>>
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kapitola">Zadejte kapitolu:</label>
                            <input type="number" id="kapitola" name="kapitola" class="search-input" value="<?php echo esc_attr($selected_chapter); ?>" placeholder="Např. 5" required>
                        </div>
                    </div>
                    <div class="form-submit">
                        <button type="submit" class="search-submit-btn">Vyhledat citace</button>
                    </div>
                </form>
            </div>

            <?php if (!empty($search_results)): ?>
                <div class="search-results-container">
                    <h2 class="results-title">Nalezené citace (<?php echo count($search_results); ?>)</h2>
                    <div class="katalog-grid">
                        <?php foreach ($search_results as $result): ?>
                            <a href="<?php echo esc_url($result['permalink']); ?>" class="katalog-button">
                                <?php echo esc_html($result['title']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif (isset($_GET['evangelista'])): ?>
                <div class="search-results-container no-results">
                    <p>Pro zadané kritérium (<?php echo esc_html($display_names[$selected_evangelist]); ?>, kapitola <?php echo esc_html($selected_chapter); ?>) nebyly nalezeny žádné příběhy.</p>
                </div>
            <?php endif; ?>

        </main>
        <?php get_sidebar(); ?>
    </div>
</div>

<?php get_footer(); ?>