<?php
/**
 * Template Name: Liturgický rok - Katalog
 * Description: Zobrazuje nadcházející neděle liturgického roku a propojuje je s příběhy.
 */

get_header(); // Načte hlavičku webu
?>

<div id="primary" class="content-area">
    
    <div class="content-container site-container">

        <main id="main" class="site-main" role="main">

            <style>
                /* Styly jsou převzaté z předchozí verze a plně funkční. */
                .liturgical-sunday-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 12px; }
                .liturgical-sunday-item .sunday-details { padding-right: 20px; flex-grow: 1; }
                .liturgical-sunday-item .sunday-details .sunday-season { font-weight: bold; color: #6c757d; margin-right: 0.5em; font-size: 1em; }
                .liturgical-sunday-item .sunday-details .sunday-name { margin: 0; font-size: 1.1rem; display: inline; }
                .liturgical-sunday-item .sunday-action { display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; align-items: flex-end; margin-left: 20px;}
                .liturgical-sunday-item .button .button-text-mobile { display: none; }
                @media (max-width: 768px) {
                    .liturgical-sunday-item { flex-direction: column; align-items: stretch; padding: 12px; }
                    .liturgical-sunday-item .sunday-details { text-align: center; margin-bottom: 12px; padding-right: 0; }
                    .liturgical-sunday-item .sunday-details .sunday-season { display: none; }
                    .liturgical-sunday-item .sunday-details .sunday-name { display: block; }
                    .liturgical-sunday-item .sunday-action { align-items: stretch; margin-left: 0; }
                    .liturgical-sunday-item .sunday-action .button, .liturgical-sunday-item .sunday-action button[disabled] { width: 100%; box-sizing: border-box; text-align: center; }
                    .liturgical-sunday-item .button .button-text-full { display: none; }
                    .liturgical-sunday-item .button .button-text-mobile { display: inline; }
                }
            </style>

            <?php
            // --- POMOCNÉ FUNKCE ---

            if (!function_exists('knihaslova_get_liturgical_cycle')) {
                function knihaslova_get_liturgical_cycle($date) {
                    $year = (int)$date->format('Y');
                    $christmas = new DateTime("$year-12-25");
                    $day_of_week = (int)$christmas->format('w');
                    $first_advent = (clone $christmas)->modify('-' . $day_of_week . ' days -3 weeks');
                    
                    $lit_year_start = ($date < $first_advent) ? $year - 1 : $year;
                    
                    $base_year = 2022; // Cyklus A začal adventem 2022
                    $diff = $lit_year_start - $base_year;
                    $cycle_index = $diff % 3;
                    if ($cycle_index < 0) { $cycle_index += 3; }
                    $cycles = ['A', 'B', 'C'];
                    return $cycles[$cycle_index];
                }
            }
            
            if (!function_exists('knihaslova_calculate_liturgical_dates')) {
                /**
                 * Vytvoří mapu liturgických nedělí a jejich skutečných kalendářních dat.
                 */
                function knihaslova_calculate_liturgical_dates($all_sundays, $cycle) {
                    $year = (int)date('Y');
                    $easter_date = new DateTime('@' . easter_date($year));
                    $ash_wednesday = (clone $easter_date)->modify('-46 days');
                    
                    $christmas = new DateTime("$year-12-25");
                    $day_of_week = (int)$christmas->format('w');
                    $first_advent_of_this_year = (clone $christmas)->modify('-' . $day_of_week . ' days -3 weeks');

                    $today = new DateTime('today');
                    $start_of_lit_year_date = ($today < $first_advent_of_this_year) 
                        ? (new DateTime(($year - 1) . '-12-25'))->modify('-' . ((int)(new DateTime(($year - 1) . '-12-25'))->format('w')) . ' days -3 weeks')
                        : $first_advent_of_this_year;

                    $sundays_in_cycle = array_values(array_filter($all_sundays, function($s) use ($cycle) {
                        return $s['Cyklus'] === $cycle || $s['Cyklus'] === 'ABC';
                    }));
                    
                    $date_map = [];
                    foreach ($sundays_in_cycle as $index => $sunday) {
                        $date_of_this_sunday = (clone $start_of_lit_year_date)->modify("+$index weeks");
                        $date_map[$sunday['ID_Nedele']] = $date_of_this_sunday;
                    }
                    return $date_map;
                }
            }


            // --- HLAVNÍ LOGIKA ---

            $all_sundays = get_option('knihaslova_liturgical_year_data');
            $all_stories_data = get_option('knihaslova_all_stories_data');
            
            if (empty($all_sundays) || empty($all_stories_data)) {
                echo '<p>Data liturgického roku nebo data příběhů nebyla nalezena. Spusťte prosím aktualizaci v administraci.</p>';
            } else {
                $today = new DateTime('today');
                $current_cycle = knihaslova_get_liturgical_cycle($today);
                
                // Vytvoříme mapu dat pro aktuální cyklus
                $date_map = knihaslova_calculate_liturgical_dates($all_sundays, $current_cycle);

                // Najdeme správný výchozí bod v seznamu
                $current_index = 0;
                $found_sunday_id = null;
                foreach ($date_map as $id => $date) {
                    if ($date >= $today) {
                        $found_sunday_id = $id;
                        break;
                    }
                }

                // Najdeme index této neděle v původním poli
                if ($found_sunday_id) {
                    foreach ($all_sundays as $index => $sunday) {
                        if ($sunday['ID_Nedele'] === $found_sunday_id && ($sunday['Cyklus'] === $current_cycle || $sunday['Cyklus'] === 'ABC')) {
                            // Začneme o jednu neděli dříve, aby byla vidět i ta aktuální/právě uplynulá
                            $current_index = ($index > 0) ? $index -1 : 0;
                            break;
                        }
                    }
                }
                
                $upcoming_sundays = array_slice($all_sundays, $current_index, 15);

                the_title('<h1 class="entry-title">', '</h1>');
                echo '<p class="liturgical-cycle-info">Následující neděle pro liturgický cyklus: <strong>' . $current_cycle . '</strong></p>';
                
                echo '<div class="liturgical-year-list">';
                
                foreach ($upcoming_sundays as $sunday) {
                    if ($sunday['Cyklus'] !== $current_cycle && $sunday['Cyklus'] !== 'ABC') {
                        continue;
                    }

                    $stories_for_sunday = [];
                    $evangelist_map = [
                        'ID_Pribehu_Mt'  => 'Matous_Citace',
                        'ID_Pribehu_Mk'  => 'Marek_Citace',
                        'ID_Pribehu_Lk'  => 'Lukas_Citace',
                        'ID_Pribehu_Jan' => 'Jan_Citace',
                    ];

                    foreach ($evangelist_map as $id_key => $citation_key) {
                        if (!empty($sunday[$id_key])) {
                            $story_ids = explode(',', $sunday[$id_key]);
                            foreach ($story_ids as $single_story_id) {
                                $single_story_id = trim($single_story_id);
                                if (isset($all_stories_data[$single_story_id])) {
                                    $stories_for_sunday[] = [
                                        'id'           => $single_story_id,
                                        'citation_key' => $citation_key,
                                        'story_data'   => $all_stories_data[$single_story_id]
                                    ];
                                }
                            }
                        }
                    }

                    ?>
                    <div class="liturgical-sunday-item <?php echo !empty($stories_for_sunday) ? 'has-story' : 'no-story'; ?>">
                        <div class="sunday-details">
                            <span class="sunday-season"><?php echo esc_html($sunday['Obdobi']); ?></span>
                            <h3 class="sunday-name"><?php echo esc_html($sunday['Nazev_Nedele']); ?></h3>
                        </div>
                        <div class="sunday-action">
                            <?php if (!empty($stories_for_sunday)): ?>
                                <?php foreach ($stories_for_sunday as $story_item): ?>
                                    <?php
                                        $story_id       = $story_item['id'];
                                        $story_info     = $story_item['story_data']['info'];
                                        $story_name     = $story_info['Nazev_Pribehu'] ?? '';
                                        $citation       = $story_info[$story_item['citation_key']] ?? '';
                                        $story_url      = home_url('/evangelijni-pribeh/' . $story_id);
                                        
                                        $full_button_text = ($citation && $story_name) ? "$citation ($story_name)" : ($story_name ?: 'Přejít na příběh');
                                        $mobile_button_text = !empty($citation) ? $citation : $story_name;
                                    ?>
                                    <a href="<?php echo esc_url($story_url); ?>" class="button button-primary">
                                        <span class="button-text-full"><?php echo esc_html($full_button_text); ?></span>
                                        <span class="button-text-mobile"><?php echo esc_html($mobile_button_text); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <button class="button" disabled>Příběh se připravuje</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                
                echo '</div>';
            }
            ?>
        
        </main>

        <?php get_sidebar(); ?>

    </div>

</div><?php

get_footer();
?>