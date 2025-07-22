<?php
/**
 * Template Name: Liturgický rok - Katalog
 * Description: Zobrazuje nadcházející neděle liturgického roku a propojuje je s příběhy.
 */

get_header(); // Načte hlavičku webu
?>

<div id="primary" class="content-area">
    
    <?php // KROK 1: PŘIDÁNÍ SPRÁVNÉHO OBALU, KTERÝ ZAJISTÍ DVOUSLOUPCOVÝ LAYOUT ?>
    <div class="content-container site-container">

        <main id="main" class="site-main" role="main">

            <?php
            // --- POMOCNÉ FUNKCE PRO VÝPOČET ---

            if (!function_exists('knihaslova_get_liturgical_cycle')) {
                function knihaslova_get_liturgical_cycle($date) {
                    $christmas = new DateTime($date->format('Y') . '-12-25');
                    $days_to_sunday = 7 - $christmas->format('w');
                    $first_advent = $christmas->modify('-3 weeks')->modify("+$days_to_sunday days");
                    $year = ($date < $first_advent) ? (int)$date->format('Y') - 1 : (int)$date->format('Y');
                    $base_year = 2022; 
                    $diff = $year - $base_year;
                    $cycle_index = $diff % 3;
                    if ($cycle_index < 0) { $cycle_index += 3; }
                    $cycles = ['A', 'B', 'C'];
                    return $cycles[$cycle_index];
                }
            }

            if (!function_exists('knihaslova_find_current_sunday_index')) {
                function knihaslova_find_current_sunday_index($liturgical_data, $current_cycle) {
                    $today_id = 'mezidobi-17'; // Prozatím statická hodnota pro testování
                    foreach ($liturgical_data as $index => $row) {
                        $cycle_match = ($row['Cyklus'] === $current_cycle || $row['Cyklus'] === 'ABC');
                        if ($row['ID_Nedele'] === $today_id && $cycle_match) {
                            return $index;
                        }
                    }
                    return 0;
                }
            }

            // --- HLAVNÍ LOGIKA ZOBRAZENÍ ---

            $all_sundays = get_option('knihaslova_liturgical_year_data');
            $all_stories_data = get_option('knihaslova_all_stories_data');
            
            if (empty($all_sundays) || empty($all_stories_data)) {
                echo '<p>Data liturgického roku nebo data příběhů nebyla nalezena. Spusťte prosím aktualizaci v administraci.</p>';
            } else {
                $today = new DateTime();
                $current_cycle = knihaslova_get_liturgical_cycle($today);
                $current_index = knihaslova_find_current_sunday_index($all_sundays, $current_cycle);
                $upcoming_sundays = array_slice($all_sundays, $current_index, 15);

                the_title('<h1 class="entry-title">', '</h1>');
                echo '<p class="liturgical-cycle-info">Následující neděle pro liturgický cyklus: <strong>' . $current_cycle . '</strong></p>';
                
                echo '<div class="liturgical-year-list">';
                
                foreach ($upcoming_sundays as $sunday) {
                    if ($sunday['Cyklus'] !== $current_cycle && $sunday['Cyklus'] !== 'ABC') {
                        continue;
                    }

                    $story_id = '';
                    $evangelist_key = '';

                    if (!empty($sunday['ID_Pribehu_Mt']))  { $story_id = $sunday['ID_Pribehu_Mt']; $evangelist_key = 'Matous_Citace'; }
                    elseif (!empty($sunday['ID_Pribehu_Mk']))  { $story_id = $sunday['ID_Pribehu_Mk']; $evangelist_key = 'Marek_Citace'; }
                    elseif (!empty($sunday['ID_Pribehu_Lk']))  { $story_id = $sunday['ID_Pribehu_Lk']; $evangelist_key = 'Lukas_Citace'; }
                    elseif (!empty($sunday['ID_Pribehu_Jan'])) { $story_id = $sunday['ID_Pribehu_Jan']; $evangelist_key = 'Jan_Citace'; }
                    
                    $has_story = !empty($story_id);
                    $button_text = 'Příběh se připravuje'; 

                    if ($has_story && isset($all_stories_data[$story_id])) {
                        $story_info = $all_stories_data[$story_id]['info'];
                        $story_name = !empty($story_info['Nazev_Pribehu']) ? $story_info['Nazev_Pribehu'] : '';
                        $citation = !empty($story_info[$evangelist_key]) ? $story_info[$evangelist_key] : '';

                        if ($citation && $story_name) {
                            $button_text = $citation . ' (' . $story_name . ')';
                        } elseif ($story_name) {
                            $button_text = $story_name;
                        } else {
                            $button_text = 'Přejít na příběh';
                        }
                    }

                    ?>
                    <div class="liturgical-sunday-item <?php echo $has_story ? 'has-story' : 'no-story'; ?>">
                        <div class="sunday-details">
                            <span class="sunday-season"><?php echo esc_html($sunday['Obdobi']); ?></span>
                            <h3 class="sunday-name"><?php echo esc_html($sunday['Nazev_Nedele']); ?></h3>
                        </div>
                        <div class="sunday-action">
                            <?php if ($has_story): ?>
                                <a href="<?php echo esc_url(home_url('/evangelijni-pribeh/' . $story_id)); ?>" class="button button-primary">
                                    <?php echo esc_html($button_text); ?>
                                </a>
                            <?php else: ?>
                                <button class="button" disabled><?php echo esc_html($button_text); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                
                echo '</div>';
            }
            ?>
        
        </main>

        <?php // KROK 2: PŘESUNUTÍ BOČNÍHO PANELU DO OBALU ?>
        <?php get_sidebar(); ?>

    </div><?php // Konec .content-container ?>

</div><?php
get_footer(); // Načte patičku webu
?>