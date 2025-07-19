<?php
// Získání dat předaných z hlavního souboru
$story_data = get_query_var('story_data');
$post_slug = get_query_var('post_slug');
if (!$story_data || !$post_slug) return;

$evangelists = ['Matous', 'Marek', 'Lukas', 'Jan'];
$display_names = ['Matous' => 'Matouš', 'Marek' => 'Marek', 'Lukas' => 'Lukáš', 'Jan' => 'Jan'];

// Zjistíme, pro které evangelisty existuje exegeze a který má být aktivní
$available_exegesis = [];
$active_evangelist_key = null;

foreach ($evangelists as $key => $evangelist) {
    $exegesis_slug = $post_slug . '-exegeze-' . strtolower($evangelist);
    $exegesis_page = get_page_by_path($exegesis_slug, OBJECT, 'page');
    if ($exegesis_page) {
        $available_exegesis[$evangelist] = $exegesis_page;
        if (is_null($active_evangelist_key)) {
            $active_evangelist_key = $evangelist;
        }
    }
}
?>

<?php if (!empty($available_exegesis)): ?>
    <div class="evangelist-switcher exegesis-switcher">
        <?php foreach ($available_exegesis as $evangelist => $page): ?>
            <button class="nav-tab <?php echo ($evangelist === $active_evangelist_key) ? 'active' : ''; ?>" data-exegesis-target="exegesis-<?php echo strtolower($evangelist); ?>">
                <?php echo esc_html($display_names[$evangelist]); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="exegesis-content-container">
        <?php foreach ($available_exegesis as $evangelist => $page):
            $is_active = ($evangelist === $active_evangelist_key);
            $katolicky_text = $story_data['translations']['katolicky'][$evangelist . '_Text'] ?? '';
            $katolicky_citace = $story_data['info'][$evangelist . '_Citace'] ?? '';
            ?>
            <div id="exegesis-<?php echo strtolower($evangelist); ?>" class="exegesis-content <?php echo $is_active ? 'active' : ''; ?>">
                <div class="content-to-copy-wrapper">
                    <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#exegesis-<?php echo strtolower($evangelist); ?> .copy-target">
                        <i class="fa fa-clone" aria-hidden="true"></i>
                    </button>
                    <div class="copy-target">
                        <?php if (!empty($katolicky_text)): ?>
                            <div class="exegesis-base-text">
                                <h4><?php echo esc_html($katolicky_citace); ?></h4>
                                <p><em><?php echo nl2br(esc_html($katolicky_text)); ?></em></p>
                            </div>
                        <?php endif; ?>
                        <div class="exegesis-main-content">
                            <?php echo apply_filters('the_content', $page->post_content); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Pro tento příběh zatím není k dispozici žádná textová exegeze.</p>
<?php endif; ?>