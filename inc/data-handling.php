<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//======================================================================
// 4. FUNKCE PRO ZPRACOVÁNÍ DAT (GOOGLE SHEETS A DALŠÍ)
//======================================================================

/**
 * Načte a zpracuje data z publikované Google Tabulky (CSV).
 * @param string $sheet_url URL publikovaného CSV souboru.
 * @return array|false Pole dat nebo false při chybě.
 */
function get_data_from_google_sheet($sheet_url) {
    $response = wp_remote_get($sheet_url, array('timeout' => 20));

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        error_log('Google Sheets Chyba: Nepodařilo se stáhnout data z URL: ' . $sheet_url);
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    // Odstraní BOM (Byte Order Mark), který může způsobovat problémy na začátku souboru
    $body = preg_replace('/^\x{FEFF}|\x{EF}\x{BB}\x{BF}/', '', $body);

    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $body);
    rewind($stream);

    $header = fgetcsv($stream);
    if ($header === false) {
        fclose($stream);
        return false;
    }

    $data = [];
    while (($row = fgetcsv($stream)) !== false) {
        if (count($header) === count($row)) {
            // Přidáme řádek pouze pokud není úplně prázdný
            if (count(array_filter($row)) > 0) {
                $data[] = array_combine($header, $row);
            }
        }
    }
    fclose($stream);

    return $data;
}

/**
 * Získá data pro daný příběh z lokálně uložených dat ve WordPressu.
 * @param string $story_id Unikátní ID příběhu (post slug).
 * @return array|null Pole s daty pro daný příběh nebo null, pokud nejsou data nalezena.
 */
function knihaslova_get_story_data($story_id) {
    if (empty($story_id)) {
        return null;
    }

    $all_stories = get_option('knihaslova_all_stories_data');

    if (!empty($all_stories) && isset($all_stories[$story_id])) {
        return $all_stories[$story_id];
    }

    error_log('Kniha Slova: Data pro příběh "' . $story_id . '" nebyla nalezena v uložených datech. Spusťte prosím manuální aktualizaci v administraci.');
    return null;
}

/**
 * Manuálně spustí proces načtení všech dat z Google Sheets a uloží je do databáze.
 * Tuto funkci volá tlačítko v administraci.
 */
function knihaslova_manual_data_update() {
    echo '<div class="notice notice-info"><p>Zahajuji aktualizaci dat z Google Sheets...</p></div>';

    // 1. DEFINICE VŠECH ZDROJOVÝCH URL
    // ===================================
    $urls = [
        'pribehy'        => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=0&single=true&output=csv',
        'katolicky'      => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=581207951&single=true&output=csv',
        'ekumenicky'     => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=1989356485&single=true&output=csv',
        'jeruzalemsky'   => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=493482207&single=true&output=csv',
        'liturgicky_rok' => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=1699666248&single=true&output=csv'
    ];

    // 2. NAČTENÍ DAT PRO LITURGICKÝ ROK
    // ===================================
    if (empty($urls['liturgicky_rok'])) {
        echo '<div class="notice notice-warning"><p><strong>Liturgický rok:</strong> Není zadána platná CSV adresa. Tento krok byl přeskočen.</p></div>';
    } else {
        $liturgicky_rok_data = get_data_from_google_sheet($urls['liturgicky_rok']);
        if ($liturgicky_rok_data !== false) {
            update_option('knihaslova_liturgical_year_data', $liturgicky_rok_data);
            echo '<div class="notice notice-success"><p><strong>Liturgický rok:</strong> Data byla úspěšně načtena a uložena. Počet záznamů: ' . count($liturgicky_rok_data) . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>Liturgický rok:</strong> Chyba při načítání dat. Zkontrolujte CSV adresu a publikování listu.</p></div>';
        }
    }

    // 3. ZPRACOVÁNÍ DAT PRO PŘÍBĚHY
    // ===============================================
    echo '<div class="notice notice-info"><p>Zpracovávám data příběhů a překladů...</p></div>';

    $pribehy_data = get_data_from_google_sheet($urls['pribehy']);
    if (!$pribehy_data) {
        echo '<div class="notice notice-error"><p><strong>Příběhy:</strong> Kritická chyba! Nepodařilo se načíst hlavní seznam příběhů. Zpracování bylo ukončeno.</p></div>';
        return false; // Ukončíme funkci s chybou
    }

    $katolicky_data = get_data_from_google_sheet($urls['katolicky']);
    $ekumenicky_data = get_data_from_google_sheet($urls['ekumenicky']);
    $jeruzalemsky_data = get_data_from_google_sheet($urls['jeruzalemsky']);

    $all_stories_data = [];
    $find_row = function($data, $id) {
        if (!$data) return null;
        foreach ($data as $row) {
            if (isset($row['ID_pribehu']) && trim($row['ID_pribehu']) === $id) {
                return $row;
            }
        }
        return null;
    };

    foreach ($pribehy_data as $story_info) {
        $story_id = trim($story_info['ID_pribehu']);
        if (empty($story_id)) continue;
        $all_stories_data[$story_id] = [
            'info' => $story_info,
            'translations' => [
                'katolicky'    => $find_row($katolicky_data, $story_id),
                'ekumenicky'   => $find_row($ekumenicky_data, $story_id),
                'jeruzalemsky' => $find_row($jeruzalemsky_data, $story_id)
            ]
        ];
    }

    $old_stories_data = get_option('knihaslova_all_stories_data');

    if ($old_stories_data == $all_stories_data) {
        echo '<div class="notice notice-info"><p><strong>Příběhy a překlady:</strong> Data jsou aktuální, nebylo potřeba nic ukládat. Počet příběhů: ' . count($all_stories_data) . '</p></div>';
    } else {
        update_option('knihaslova_all_stories_data', $all_stories_data);
        echo '<div class="notice notice-success"><p><strong>Příběhy a překlady:</strong> Nová data byla úspěšně zpracována a uložena. Počet příběhů: ' . count($all_stories_data) . '</p></div>';
    }
    
    return true; 
}

/**
 * Převede biblickou citaci na číslo pro snadné řazení.
 * @param string $citation Citace ve formátu "Mt 3,1-12".
 * @return int Číselná hodnota pro řazení.
 */
function knihaslova_get_citation_sort_key($citation) {
    if (preg_match('/(\d+),\s*(\d+)/', $citation, $matches)) {
        $chapter = (int)$matches[1];
        $verse_start = (int)$matches[2];
        return $chapter * 1000 + $verse_start;
    }
    return 999999;
}

// --- START: New function for Liturgical Readings ---
/**
 * Retrieves data for a specific Sunday from the saved 'knihaslova_liturgical_year_data' option.
 *
 * @param string $id The unique ID of the Sunday (from column 'ID_Nedele').
 * @return array|null The row data for the Sunday as an associative array, or null if not found.
 */
function knihaslova_get_sunday_data_by_id( $id ) {
    $all_sundays = get_option( 'knihaslova_liturgical_year_data' );

    if ( ! $all_sundays || ! is_array( $all_sundays ) ) {
        return null;
    }

    foreach ( $all_sundays as $sunday_row ) {
        // Check if the key 'ID_Nedele' exists and if its value matches the requested ID.
        if ( isset( $sunday_row['ID_Nedele'] ) && trim( $sunday_row['ID_Nedele'] ) === $id ) {
            return $sunday_row; // Return the associative array for the matching Sunday.
        }
    }

    return null; // Return null if no match was found.
}
// --- END: New function for Liturgical Readings ---

