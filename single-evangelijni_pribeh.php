<?php
get_header();

$post_slug = get_post_field( 'post_name', get_the_ID() );
$story_data = knihaslova_get_story_data($post_slug);

// Načtení stránky pro Srovnání (dříve SHRNUTÍ)
$comparison_page_slug = $post_slug . '-srovnani';
$comparison_page = get_page_by_path($comparison_page_slug, OBJECT, 'page');

// --- PŘIDÁNO: Načtení stránky pro Podcast ---
$podcast_page_slug = $post_slug . '-podcast';
$podcast_page = get_page_by_path($podcast_page_slug, OBJECT, 'page');

// --- PŘIDÁNO: Načtení stránky pro Parafrázi ---
$parafraze_page_slug = $post_slug . '-parafraze';
$parafraze_page = get_page_by_path($parafraze_page_slug, OBJECT, 'page');
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <div class="container story-detail">

                <div class="page-header-container">
                    <h1 class="page-title"><?php echo esc_html($story_data['info']['Nazev_pribehu'] ?? get_the_title()); ?></h1>
                </div>

                <div class="view-switcher">
                    <button class="nav-tab active" data-target="evangelists-view">Evangelisté</button>
                    <button class="nav-tab" data-target="translations-view">Překlady</button>
                    <button class="nav-tab" data-target="analysis-view">Exegeze</button>
                    <button class="nav-tab" data-target="spiritual-view">Výklad</button>

                    <?php if ($comparison_page): ?>
                        <button class="nav-tab" data-target="text-comparison-view">Srovnání</button>
                    <?php endif; ?>

                    <?php // --- PŘIDÁNO: Tlačítko pro Parafrázi --- ?>
                    <?php if ($parafraze_page): ?>
                        <button class="nav-tab" data-target="parafraze-view">Spojení</button>
                    <?php endif; ?>
                    
                    <?php // --- PŘIDÁNO: Tlačítko pro AI Podcast --- ?>
                    <?php if ($podcast_page): ?>
                        <button class="nav-tab" data-target="podcast-view">Podcast</button>
                    <?php endif; ?>
                </div>

                <div id="evangelists-view" class="tab-content active">
                    <?php set_query_var('story_data', $story_data); get_template_part('template-parts/content-evangelists'); ?>
                </div>

                <div id="translations-view" class="tab-content">
                    <?php set_query_var('story_data', $story_data); get_template_part('template-parts/content-translations'); ?>
                </div>

                <div id="analysis-view" class="tab-content">
                    <?php
                    set_query_var('story_data', $story_data);
                    set_query_var('post_slug', $post_slug);
                    get_template_part('template-parts/content-exegesis');
                    ?>
                </div>
                
                <div id="spiritual-view" class="tab-content">
                    <?php
                    set_query_var('story_data', $story_data);
                    set_query_var('post_slug', $post_slug);
                    get_template_part('template-parts/content-spiritual');
                    ?>
                </div>

                <?php if ($comparison_page): ?>
<div id="text-comparison-view" class="tab-content">
    <div class="analysis-content content-to-copy-wrapper">
        <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#text-comparison-view .copy-target">
            <i class="fa fa-clone" aria-hidden="true"></i>
        </button>
        <div class="copy-target">
            <?php echo apply_filters('the_content', $comparison_page->post_content); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php // --- PŘIDÁNO: Kontejner pro Parafrázi --- ?>
<?php if ($parafraze_page): ?>
<div id="parafraze-view" class="tab-content">
    <div class="analysis-content content-to-copy-wrapper">
        <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#parafraze-view .copy-target">
            <i class="fa fa-clone" aria-hidden="true"></i>
        </button>
        <div class="copy-target">
            <?php echo apply_filters('the_content', $parafraze_page->post_content); ?>
        </div>
    </div>
</div>
<?php endif; ?>

                <?php // --- PŘIDÁNO: Kontejner pro AI Podcast --- ?>
                <?php if ($podcast_page): ?>
                <div id="podcast-view" class="tab-content">
                    <div class="podcast-player-container">
                        <?php
                        // Přímo vykreslíme obsah. Pokud je v obsahu URL k audio souboru,
                        // WordPress by měl automaticky vytvořit přehrávač (oEmbed).
                        echo apply_filters('the_content', $podcast_page->post_content);
                        ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </main>
        <?php get_sidebar(); ?>
    </div>
</div>

<?php
get_footer();
?>