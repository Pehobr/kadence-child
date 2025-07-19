<?php
// Získání dat předaných z hlavního souboru
$story_data = get_query_var('story_data');
if (!$story_data) return;

$evangelists = ['Matous', 'Marek', 'Lukas', 'Jan'];
$translations = $story_data['translations'];
$info = $story_data['info'];

// Pole pro zobrazení jmen s diakritikou
$display_names = [
    'Matous' => 'Matouš',
    'Marek'  => 'Marek',
    'Lukas'  => 'Lukáš',
    'Jan'    => 'Jan'
];

// Najít prvního evangelistu, který má alespoň jeden překlad
$active_evangelist = null;
foreach ($evangelists as $evangelist) {
    if (!empty($translations['katolicky'][$evangelist . '_Text']) || !empty($translations['ekumenicky'][$evangelist . '_Text']) || !empty($translations['jeruzalemsky'][$evangelist . '_Text'])) {
        $active_evangelist = $evangelist;
        break;
    }
}
?>

<div class="evangelist-switcher">
    <?php foreach ($evangelists as $evangelist): ?>
        <?php
        // Zjistíme, jestli pro daného evangelistu existuje nějaký text
        $has_text = !empty($translations['katolicky'][$evangelist . '_Text']) || !empty($translations['ekumenicky'][$evangelist . '_Text']) || !empty($translations['jeruzalemsky'][$evangelist . '_Text']);
        if ($has_text):
            $is_active = ($evangelist === $active_evangelist);
        ?>
            <button class="nav-tab <?php echo $is_active ? 'active' : ''; ?>" data-target="#evangelist-<?php echo strtolower($evangelist); ?>">
                <?php echo esc_html($display_names[$evangelist]); // Zobrazení jména s diakritikou ?>
            </button>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<div class="translations-content">
    <?php foreach ($evangelists as $evangelist): ?>
        <?php
        $has_text = !empty($translations['katolicky'][$evangelist . '_Text']) || !empty($translations['ekumenicky'][$evangelist . '_Text']) || !empty($translations['jeruzalemsky'][$evangelist . '_Text']);
        if ($has_text):
            $is_active = ($evangelist === $active_evangelist);
            $evangelist_citation = $info[$evangelist . '_Citace'] ?? '';
        ?>
            <div id="evangelist-<?php echo strtolower($evangelist); ?>" class="evangelist-translation-content <?php echo $is_active ? 'active' : ''; ?>">
                <h4><?php echo esc_html($display_names[$evangelist]); // Zobrazení jména s diakritikou ?> <span>(<?php echo $evangelist_citation; ?>)</span></h4>
                <div class="comparison-grid">
                    <div class="grid-column">
                        <h3>Katolický překlad</h3>
                        <div class="text-content">
                            <?php echo nl2br(esc_html($translations['katolicky'][$evangelist . '_Text'] ?? 'Text není dostupný.')); ?>
                        </div>
                    </div>
                    <div class="grid-column">
                        <h3>Ekumenický překlad</h3>
                        <div class="text-content">
                            <?php echo nl2br(esc_html($translations['ekumenicky'][$evangelist . '_Text'] ?? 'Text není dostupný.')); ?>
                        </div>
                    </div>
                    <div class="grid-column">
                        <h3>Jeruzalémský překlad</h3>
                        <div class="text-content">
                            <?php echo nl2br(esc_html($translations['jeruzalemsky'][$evangelist . '_Text'] ?? 'Text není dostupný.')); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>