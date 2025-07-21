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

    // Získá všechna data z jedné WordPress volby (option)
    $all_stories = get_option('knihaslova_all_stories_data');

    // Zkontroluje, zda data existují a zda obsahují klíč pro požadovaný příběh
    if (!empty($all_stories) && isset($all_stories[$story_id])) {
        return $all_stories[$story_id];
    }

    // Pokud data nejsou nalezena, zapíše chybu a vrátí null
    error_log('Kniha Slova: Data pro příběh "' . $story_id . '" nebyla nalezena v uložených datech. Spusťte prosím manuální aktualizaci v administraci.');
    return null;
}

/**
 * Manuálně spustí proces načtení všech dat z Google Sheets a uloží je do databáze.
 * Tuto funkci volá tlačítko v administraci.
 * @return bool True v případě úspěchu, false při selhání.
 */
function knihaslova_manual_data_update() {
    $urls = [
        'pribehy'      => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=0&single=true&output=csv',
        'katolicky'    => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=581207951&single=true&output=csv',
        'ekumenicky'   => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=1989356485&single=true&output=csv',
        'jeruzalemsky' => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vSjUiTc1VHd8teOLlQF51n5PLw1Z7MffXrovWmjfuypO5qR0ZV-vOE1oEZ2fFn95RvjpToiwFepiMm0/pub?gid=493482207&single=true&output=csv',
    ];

    $pribehy_data = get_data_from_google_sheet($urls['pribehy']);
    if (!$pribehy_data) {
        return false; // Selhalo načtení hlavního seznamu příběhů
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
        if (empty($story_id)) {
            continue;
        }

        $all_stories_data[$story_id] = [
            'info' => $story_info,
            'translations' => [
                'katolicky'    => $find_row($katolicky_data, $story_id),
                'ekumenicky'   => $find_row($ekumenicky_data, $story_id),
                'jeruzalemsky' => $find_row($jeruzalemsky_data, $story_id)
            ]
        ];
    }

    // Uloží kompletní data do jedné volby (option) v databázi WordPressu.
    // Přepíše stávající data.
    update_option('knihaslova_all_stories_data', $all_stories_data);
    
    return true; // Úspěch
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