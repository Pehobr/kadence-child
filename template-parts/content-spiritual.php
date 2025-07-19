<?php
// Získání dat předaných z hlavního souboru
$story_data = get_query_var('story_data');
$post_slug = get_query_var('post_slug');
if (!$story_data || !$post_slug) return;

$evangelists = ['Matous', 'Marek', 'Lukas', 'Jan'];
$display_names = ['Matous' => 'Matouš', 'Marek' => 'Marek', 'Lukas' => 'Lukáš', 'Jan' => 'Jan'];

// Zjistíme, pro které evangelisty existuje duchovní výklad
$available_spiritual = [];
$active_evangelist_key = null;

foreach ($evangelists as $key => $evangelist) {
    $spiritual_slug = $post_slug . '-vyklad-' . strtolower($evangelist);
    $spiritual_page = get_page_by_path($spiritual_slug, OBJECT, 'page');
    if ($spiritual_page) {
        $available_spiritual[$evangelist] = $spiritual_page;
        if (is_null($active_evangelist_key)) {
            $active_evangelist_key = $evangelist;
        }
    }
}
?>

<?php if (!empty($available_spiritual)): ?>
    <div class="evangelist-switcher spiritual-switcher">
        <?php foreach ($available_spiritual as $evangelist => $page): ?>
            <button class="nav-tab <?php echo ($evangelist === $active_evangelist_key) ? 'active' : ''; ?>" data-target="#spiritual-<?php echo strtolower($evangelist); ?>">
                <?php echo esc_html($display_names[$evangelist]); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="spiritual-content-container">
        <?php foreach ($available_spiritual as $evangelist => $page):
            $is_active = ($evangelist === $active_evangelist_key);
            $katolicky_text = $story_data['translations']['katolicky'][$evangelist . '_Text'] ?? '';
            $katolicky_citace = $story_data['info'][$evangelist . '_Citace'] ?? '';
            ?>
            <div id="spiritual-<?php echo strtolower($evangelist); ?>" class="spiritual-content <?php echo $is_active ? 'active' : ''; ?>">
                <div class="content-to-copy-wrapper">
                     <button class="copy-to-clipboard-btn" title="Zkopírovat text" data-clipboard-target="#spiritual-<?php echo strtolower($evangelist); ?> .copy-target">
                        <i class="fa fa-clone" aria-hidden="true"></i>
                    </button>
                    <div class="copy-target">
                        <?php if (!empty($katolicky_text)): ?>
                            <div class="exegesis-base-text">
                                <h4><?php echo esc_html($katolicky_citace); ?></h4>
                                <p><em><?php echo nl2br(esc_html($katolicky_text)); ?></em></p>
                            </div>
                        <?php endif; ?>
                        <div class="spiritual-main-content">
                            <?php echo apply_filters('the_content', $page->post_content); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Pro tento příběh zatím není k dispozici žádný duchovní výklad.</p>
<?php endif; ?>