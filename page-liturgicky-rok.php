<?php
/**
 * Template Name: Liturgický rok - Katalog
 * Description: Zobrazuje nadcházející neděle liturgického roku a propojuje je s příběhy.
 */

get_header(); // Načte hlavičku webu

// ZMĚNA ZDE: Použijeme správnou funkci pro kontrolu přístupu
$is_access_granted = function_exists('knihaslova_is_access_granted') && knihaslova_is_access_granted();
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main" role="main">

            <?php
            // --- POMOCNÉ FUNKCE (Z PŮVODNÍHO KÓDU) ---

            if (!function_exists('knihaslova_get_liturgical_cycle')) {
                function knihaslova_get_liturgical_cycle($date) {
                    $year = (int)$date->format('Y');
                    $christmas = new DateTime("$year-12-25");
                    $day_of_week = (int)$christmas->format('w');
                    $first_advent = (clone $christmas)->modify('-' . $day_of_week . ' days -3 weeks');
                    $lit_year_start_number = ($date < $first_advent) ? $year : $year + 1;
                    $base_year_A = get_option('knihaslova_liturgical_cycle_base_year_A', 2023);
                    $diff = $lit_year_start_number - $base_year_A;
                    $cycle_index = $diff % 3;
                    if ($cycle_index < 0) { $cycle_index += 3; }
                    $cycles = ['A', 'B', 'C'];
                    return $cycles[$cycle_index];
                }
            }

            if (!function_exists('knihaslova_calculate_liturgical_dates_with_override')) {
                function knihaslova_calculate_liturgical_dates_with_override($all_sundays, $cycle) {
                    $ref_date_str = get_option('knihaslova_override_reference_date');
                    $ref_sunday_id = get_option('knihaslova_override_reference_sunday_id');
                    $sundays_in_cycle = array_values(array_filter($all_sundays, function($s) use ($cycle) {
                        return isset($s['Cyklus']) && ($s['Cyklus'] === $cycle || $s['Cyklus'] === 'ABC');
                    }));
                    $date_map = [];
                    if (!empty($ref_date_str) && !empty($ref_sunday_id)) {
                        $reference_date = new DateTime($ref_date_str);
                        $reference_index = -1;
                        foreach ($sundays_in_cycle as $index => $sunday) {
                            if ($sunday['ID_Nedele'] === $ref_sunday_id) {
                                $reference_index = $index;
                                break;
                            }
                        }
                        if ($reference_index !== -1) {
                            foreach ($sundays_in_cycle as $index => $sunday) {
                                $week_diff = $index - $reference_index;
                                $date_of_this_sunday = (clone $reference_date)->modify(($week_diff >= 0 ? '+' : '') . $week_diff . ' weeks');
                                $date_map[$sunday['ID_Nedele']] = $date_of_this_sunday;
                            }
                            return $date_map;
                        }
                    }
                    $year = (int)date('Y');
                    $christmas_this_year = new DateTime("$year-12-25");
                    $day_of_week_this_year = (int)$christmas_this_year->format('w');
                    $first_advent_of_this_year = (clone $christmas_this_year)->modify('-' . $day_of_week_this_year . ' days -3 weeks');
                    $today = new DateTime('today');
                    if ($today >= $first_advent_of_this_year) {
                        $start_of_lit_year_date = $first_advent_of_this_year;
                    } else {
                        $year_minus_one = $year - 1;
                        $christmas_last_year = new DateTime("$year_minus_one-12-25");
                        $day_of_week_last_year = (int)$christmas_last_year->format('w');
                        $start_of_lit_year_date = (clone $christmas_last_year)->modify('-' . $day_of_week_last_year . ' days -3 weeks');
                    }
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
                the_post();
                the_title('<h1 class="entry-title">', '</h1>');
                $today = new DateTime('today');
                $current_cycle = knihaslova_get_liturgical_cycle($today);
                $date_map = knihaslova_calculate_liturgical_dates_with_override($all_sundays, $current_cycle);
                uasort($date_map, function($a, $b) { return $a <=> $b; });
                $found_sunday_id = null;
                foreach ($date_map as $id => $date) {
                    if ($date >= $today) {
                        $found_sunday_id = $id;
                        break;
                    }
                }
                $sundays_for_display = [];
                foreach ($date_map as $id => $date) {
                    foreach($all_sundays as $sunday_data) {
                        if (isset($sunday_data['ID_Nedele']) && $sunday_data['ID_Nedele'] === $id) {
                            $sundays_for_display[] = $sunday_data;
                            break;
                        }
                    }
                }
                $current_index = 0;
                if ($found_sunday_id) {
                    foreach ($sundays_for_display as $index => $sunday) {
                        if ($sunday['ID_Nedele'] === $found_sunday_id) {
                            $current_index = ($index > 0) ? $index - 1 : 0;
                            break;
                        }
                    }
                }
                $upcoming_sundays = array_slice($sundays_for_display, $current_index, 15);
                echo '<p class="liturgical-cycle-info">Následující neděle pro liturgický cyklus: <strong>' . esc_html($current_cycle) . '</strong></p>';
                echo '<div class="liturgical-year-list">';
                foreach ($upcoming_sundays as $sunday) {
                    $stories_for_sunday = [];
                    $evangelist_map = [
                        'ID_Pribehu_Mt'  => 'Matous_Citace', 'ID_Pribehu_Mk'  => 'Marek_Citace',
                        'ID_Pribehu_Lk'  => 'Lukas_Citace', 'ID_Pribehu_Jan' => 'Jan_Citace',
                    ];
                    foreach ($evangelist_map as $id_key => $citation_key) {
                        if (!empty($sunday[$id_key])) {
                            $story_ids = explode(',', $sunday[$id_key]);
                            foreach ($story_ids as $single_story_id) {
                                $single_story_id = trim($single_story_id);
                                if (isset($all_stories_data[$single_story_id])) {
                                    $stories_for_sunday[] = [
                                        'id' => $single_story_id, 'citation_key' => $citation_key,
                                        'story_data' => $all_stories_data[$single_story_id]
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
                                        $story_id = $story_item['id'];
                                        $story_info = $story_item['story_data']['info'];
                                        $story_name = $story_info['Nazev_Pribehu'] ?? '';
                                        $citation = $story_info[$story_item['citation_key']] ?? '';
                                        $story_url = home_url('/pribeh/' . $story_id);
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
                            <?php
                            // --- START: Liturgical Readings for Priests ---
                            $sunday_id = isset($sunday['ID_Nedele']) ? trim($sunday['ID_Nedele']) : '';
                            $first_reading_citation = isset($sunday['1_cteni_citace']) ? trim($sunday['1_cteni_citace']) : '';
                            $second_reading_citation = isset($sunday['2_cteni_citace']) ? trim($sunday['2_cteni_citace']) : '';

                            // ZMĚNA ZDE: Použijeme správnou proměnnou $is_access_granted
                            if ($is_access_granted && $sunday_id && (!empty($first_reading_citation) || !empty($second_reading_citation))) {
                                if (!empty($stories_for_sunday)) {
                                    echo '<hr class="buttons-separator-katalog">';
                                }
                                echo '<div class="reading-buttons-katalog">';
                                if (!empty($first_reading_citation)) {
                                    printf(
                                        '<a href="%s" class="button button-reading">%s</a>',
                                        esc_url(home_url('/cteni/' . $sunday_id . '/1/')),
                                        esc_html($first_reading_citation)
                                    );
                                }
                                if (!empty($second_reading_citation)) {
                                    printf(
                                        '<a href="%s" class="button button-reading">%s</a>',
                                        esc_url(home_url('/cteni/' . $sunday_id . '/2/')),
                                        esc_html($second_reading_citation)
                                    );
                                }
                                echo '</div>';
                            }
                            // --- END: Liturgical Readings for Priests ---
                            ?>
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
</div>
<?php
get_footer();
?>