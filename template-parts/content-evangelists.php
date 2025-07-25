<?php
// Získání dat předaných z hlavního souboru
$story_data = get_query_var('story_data');
if (!$story_data) return;

$info = $story_data['info'];
$all_evangelists = ['Matous', 'Marek', 'Lukas', 'Jan'];

// Pole pro zobrazení jmen s diakritikou
$display_names = [
    'Matous' => 'Matouš',
    'Marek'  => 'Marek',
    'Lukas'  => 'Lukáš',
    'Jan'    => 'Jan'
];

// Zkusíme najít první dostupný překlad pro zobrazení
$first_available_translation = null;
if (!empty($story_data['translations']['katolicky'])) {
    $first_available_translation = $story_data['translations']['katolicky'];
} elseif (!empty($story_data['translations']['ekumenicky'])) {
    $first_available_translation = $story_data['translations']['ekumenicky'];
} elseif (!empty($story_data['translations']['jeruzalemsky'])) {
    $first_available_translation = $story_data['translations']['jeruzalemsky'];
}

// 1. Zjistíme, kteří evangelisté mají skutečně text
$available_evangelists = [];
if ($first_available_translation) {
    foreach ($all_evangelists as $evangelist) {
        if (!empty($first_available_translation[$evangelist . '_Text'])) {
            $available_evangelists[] = $evangelist;
        }
    }
}

// 2. Přidáme na kontejner třídu s počtem dostupných evangelistů
$column_count = count($available_evangelists);
$grid_class = 'evangelist-count-' . $column_count;

?>

<?php if ($column_count > 0): ?>
    <div class="comparison-grid <?php echo $grid_class; ?>">
        <?php foreach ($available_evangelists as $evangelist): ?>
            <div class="grid-column">
                <h3><?php echo esc_html($display_names[$evangelist]); ?></h3>
                <p class="citation"><em><?php echo esc_html($info[$evangelist . '_Citace'] ?? ''); ?></em></p>
                <div class="text-content">
                    <?php echo nl2br(esc_html($first_available_translation[$evangelist . '_Text'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>Pro tento příběh nejsou k dispozici žádné texty evangelistů.</p>
<?php endif; ?>