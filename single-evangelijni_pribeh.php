<?php
get_header();

// Zjistíme, zda má uživatel přístup k neveřejnému obsahu
$is_access_granted = function_exists('knihaslova_is_access_granted') && knihaslova_is_access_granted();

// Základní data o příběhu
$post_slug = get_post_field( 'post_name', get_the_ID() );
$story_data = function_exists('knihaslova_get_story_data') ? knihaslova_get_story_data($post_slug) : [];

// Načtení dat pro jednotlivé záložky
$comparison_page_slug = $post_slug . '-srovnani';
$comparison_page = get_page_by_path($comparison_page_slug, OBJECT, 'page');

$parafraze_page_slug = $post_slug . '-parafraze';
$parafraze_page = get_page_by_path($parafraze_page_slug, OBJECT, 'page');

$podcast_page_slug = $post_slug . '-podcast';
$podcast_page = get_page_by_path($podcast_page_slug, OBJECT, 'page');

// --- NOVÉ: Načtení stránky pro infografiku ---
$infografika_page_slug = $post_slug . '-infografika';
$infografika_page = get_page_by_path($infografika_page_slug, OBJECT, 'page');

// Načtení stránky pro záložku "Pro kněze"
$pro_kneze_page_slug = $post_slug . '-pro-kneze';
$pro_kneze_page = get_page_by_path($pro_kneze_page_slug, OBJECT, 'page');
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <div class="container story-detail">

                <div class="page-header-container">
                    <h1 class="page-title"><?php echo esc_html($story_data['info']['Nazev_pribehu'] ?? get_the_title()); ?></h1>
                </div>

                <div class="view-switcher">
                    <?php // --- Veřejné záložky --- ?>
                    <button class="nav-tab active" data-target="evangelists-view">Evangelisté</button>
                    <button class="nav-tab" data-target="translations-view">Překlady</button>
                    <button class="nav-tab" data-target="vyklad-view">Výklad</button>
                    
                    <?php // --- ZÁLOŽKA DALŠÍ (s podzáložkami) --- ?>
                    <?php if ($comparison_page || $parafraze_page || $podcast_page || $infografika_page): ?>
                        <button class="nav-tab" data-target="dalsi-view">Další</button>
                    <?php endif; ?>

                    <?php // --- Chráněná záložka --- ?>
                    <?php if ($is_access_granted && $pro_kneze_page): ?>
                        <button class="nav-tab" data-target="pro-kneze-view">Pro kněze</button>
                    <?php endif; ?>
                </div>

                <?php // --- Obsah veřejných záložek --- ?>
                <div id="evangelists-view" class="tab-content active">
                    <?php set_query_var('story_data', $story_data); get_template_part('template-parts/content-evangelists'); ?>
                </div>
                <div id="translations-view" class="tab-content">
                    <?php set_query_var('story_data', $story_data); get_template_part('template-parts/content-translations'); ?>
                </div>
                <div id="vyklad-view" class="tab-content">
                    <div class="evangelist-switcher sub-switcher">
                        <button class="nav-tab active" data-target="#analysis-view">Exegeze</button>
                        <button class="nav-tab" data-target="#spiritual-view">Duchovní výklad</button>
                    </div>
                    <div class="sub-content-container">
                        <div id="analysis-view" class="tab-content-pane active">
                            <?php set_query_var('story_data', $story_data); set_query_var('post_slug', $post_slug); get_template_part('template-parts/content-exegesis'); ?>
                        </div>
                        <div id="spiritual-view" class="tab-content-pane">
                            <?php set_query_var('story_data', $story_data); set_query_var('post_slug', $post_slug); get_template_part('template-parts/content-spiritual'); ?>
                        </div>
                    </div>
                </div>

                <?php // --- OBSAH ZÁLOŽKY DALŠÍ --- ?>
                <?php if ($comparison_page || $parafraze_page || $podcast_page || $infografika_page): ?>
                <div id="dalsi-view" class="tab-content">
                    
                    <div class="evangelist-switcher sub-switcher">
                        <?php $dalsi_tab_is_active = true; ?>
                        <?php if ($comparison_page): ?>
                            <button class="nav-tab <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>" data-target="#text-comparison-view">Srovnání</button>
                        <?php endif; ?>
                        <?php if ($parafraze_page): ?>
                            <button class="nav-tab <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>" data-target="#parafraze-view">Spojení</button>
                        <?php endif; ?>
                        <?php if ($podcast_page): ?>
                            <button class="nav-tab <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>" data-target="#podcast-view">Podcast</button>
                        <?php endif; ?>
                        <?php // --- NOVÉ: Tlačítko pro infografiku --- ?>
                        <?php if ($infografika_page): ?>
                            <button class="nav-tab <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>" data-target="#infografika-view">Infografika</button>
                        <?php endif; ?>
                    </div>

                    <div class="sub-content-container">
                        <?php $dalsi_tab_is_active = true; ?>
                        <?php if ($comparison_page): ?>
                            <div id="text-comparison-view" class="tab-content-pane <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>">
                                <div class="analysis-content content-to-copy-wrapper">
                                    <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#text-comparison-view .copy-target"><i class="fa fa-clone" aria-hidden="true"></i></button>
                                    <div class="copy-target"><?php echo apply_filters('the_content', $comparison_page->post_content); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($parafraze_page): ?>
                            <div id="parafraze-view" class="tab-content-pane <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>">
                                <div class="analysis-content content-to-copy-wrapper">
                                    <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#parafraze-view .copy-target"><i class="fa fa-clone" aria-hidden="true"></i></button>
                                    <div class="copy-target"><?php echo apply_filters('the_content', $parafraze_page->post_content); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($podcast_page): ?>
                             <div id="podcast-view" class="tab-content-pane <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>">
                                <div class="podcast-player-container"><?php echo apply_filters('the_content', $podcast_page->post_content); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php // --- NOVÉ: Obsah pro infografiku --- ?>
                        <?php if ($infografika_page): ?>
                            <div id="infografika-view" class="tab-content-pane <?php if ($dalsi_tab_is_active) { echo 'active'; $dalsi_tab_is_active = false; } ?>">
                                <div class="infographic-content">
                                    <?php echo apply_filters('the_content', $infografika_page->post_content); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php // --- Obsah chráněné záložky --- ?>
                <?php if ($is_access_granted && $pro_kneze_page): ?>
                    <div id="pro-kneze-view" class="tab-content">
                        <div class="analysis-content content-to-copy-wrapper">
                            <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#pro-kneze-view .copy-target"><i class="fa fa-clone" aria-hidden="true"></i></button>
                            <div class="copy-target"><?php echo apply_filters('the_content', $pro_kneze_page->post_content); ?></div>
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