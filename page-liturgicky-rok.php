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

            <?php
            // --- STYLY PRO STRÁNKU ---
            ?>
            <style>
                /* Základní styly pro desktop */
                .liturgical-sunday-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px 20px;
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 8px;
                    margin-bottom: 12px;
                }
                .liturgical-sunday-item .sunday-details {
                    padding-right: 20px;
                    flex-grow: 1; /* Detail zabere co nejvíc místa */
                }
                .liturgical-sunday-item .sunday-details .sunday-season {
                    font-weight: bold;
                    color: #6c757d;
                    margin-right: 0.5em;
                    font-size: 1em;
                }
                .liturgical-sunday-item .sunday-details .sunday-name {
                    margin: 0;
                    font-size: 1.1rem;
                    display: inline;
                }
                
                /* === ZMĚNA ZDE: Kontejner pro tlačítka === */
                .liturgical-sunday-item .sunday-action {
                    display: flex;
                    flex-direction: column; /* Tlačítka budou vždy pod sebou */
                    gap: 8px;              /* Mezera mezi tlačítky */
                    flex-shrink: 0;        /* Zabrání smrštění kontejneru */
                    align-items: flex-end;   /* Zarovnání tlačítek doprava */
                }
                
                .liturgical-sunday-item .button .button-text-mobile {
                    display: none;
                }

                /* Styly pro mobilní zobrazení (do 768px) */
                @media (max-width: 768px) {
                    .liturgical-sunday-item {
                        flex-direction: column;
                        align-items: stretch;
                        padding: 12px;
                    }
                    .liturgical-sunday-item .sunday-details {
                        text-align: center;
                        margin-bottom: 12px;
                        padding-right: 0;
                    }
                    .liturgical-sunday-item .sunday-details .sunday-season {
                        display: none;
                    }
                    .liturgical-sunday-item .sunday-details .sunday-name {
                        display: block;
                    }
                    
                    /* === ZMĚNA ZDE: Kontejner tlačítek na mobilu === */
                    .liturgical-sunday-item .sunday-action {
                        align-items: stretch; /* Tlačítka se roztáhnou na 100% */
                    }

                    .liturgical-sunday-item .sunday-action .button,
                    .liturgical-sunday-item .sunday-action button[disabled] {
                        width: 100%;
                        box-sizing: border-box;
                        text-align: center;
                    }
                    .liturgical-sunday-item .button .button-text-full {
                        display: none;
                    }
                    .liturgical-sunday-item .button .button-text-mobile {
                        display: inline;
                    }
                }
            </style>

            <?php
            // --- POMOCNÉ FUNKCE ---

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

            // --- HLAVNÍ LOGIKA ---

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

                    // === ZMĚNA ZDE: Najdeme VŠECHNY příběhy, ne jen první ===
                    $stories_for_sunday = [];
                    $evangelist_map = [
                        'ID_Pribehu_Mt'  => 'Matous_Citace',
                        'ID_Pribehu_Mk'  => 'Marek_Citace',
                        'ID_Pribehu_Lk'  => 'Lukas_Citace',
                        'ID_Pribehu_Jan' => 'Jan_Citace',
                    ];

                    // Projdeme všechny možné sloupce pro ID příběhů
                    foreach ($evangelist_map as $id_key => $citation_key) {
                        if (!empty($sunday[$id_key]) && isset($all_stories_data[$sunday[$id_key]])) {
                            $stories_for_sunday[] = [
                                'id'             => $sunday[$id_key],
                                'citation_key'   => $citation_key,
                                'story_data'     => $all_stories_data[$sunday[$id_key]]
                            ];
                        }
                    }
                    // === KONEC ZMĚNY ===

                    ?>
                    <div class="liturgical-sunday-item <?php echo !empty($stories_for_sunday) ? 'has-story' : 'no-story'; ?>">
                        <div class="sunday-details">
                            <span class="sunday-season"><?php echo esc_html($sunday['Obdobi']); ?></span>
                            <h3 class="sunday-name"><?php echo esc_html($sunday['Nazev_Nedele']); ?></h3>
                        </div>
                        <div class="sunday-action">
                            <?php if (!empty($stories_for_sunday)): ?>
                                <?php foreach ($stories_for_sunday as $story_item): // Projdeme všechny nalezené příběhy ?>
                                    <?php
                                        // Příprava dat pro konkrétní tlačítko
                                        $story_id       = $story_item['id'];
                                        $story_info     = $story_item['story_data']['info'];
                                        $story_name     = $story_info['Nazev_Pribehu'] ?? '';
                                        $citation       = $story_info[$story_item['citation_key']] ?? '';
                                        $story_url      = home_url('/evangelijni-pribeh/' . $story_id);
                                        
                                        $full_button_text = ($citation && $story_name) ? "$citation ($story_name)" : ($story_name ?: 'Přejít na příběh');
                                    ?>
                                    <a href="<?php echo esc_url($story_url); ?>" class="button button-primary">
                                        <span class="button-text-full"><?php echo esc_html($full_button_text); ?></span>
                                        <span class="button-text-mobile"><?php echo esc_html($citation); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <button class="button" disabled>Příběh se připravuje</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                
                echo '</div>'; // konec .liturgical-year-list
            }
            ?>
        
        </main>

        <?php get_sidebar(); ?>

    </div><?php // Konec .content-container ?>

</div><?php

get_footer(); // Načte patičku webu
?>