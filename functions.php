<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Kniha Slova - Hlavní soubor pro funkce šablony.
 *
 * Tento soubor slouží jako "rozcestník" a načítá všechny
 * logické části kódu z podadresáře /inc.
 *
 * @package Kadence Child
 */

// Cesta k adresáři s funkcemi
$inc_dir = get_stylesheet_directory() . '/inc/';

// 1. Základní nastavení child šablony
require_once $inc_dir . 'theme-setup.php';

// 2. Registrace vlastního typu příspěvku (Custom Post Type)
require_once $inc_dir . 'post-types.php';

// 3. Načítání vlastních CSS a JS souborů
require_once $inc_dir . 'enqueue-scripts.php';

// 4. Funkce pro zpracování dat (Google Sheets a další)
require_once $inc_dir . 'data-handling.php';

// 5. Vývojářská sekce pro správu webu
require_once $inc_dir . 'admin-settings.php';

// 6. Vlastní úpravy a funkce
require_once $inc_dir . 'custom-functions.php';

/**
 * Načtení vlastních CSS stylů pro child šablonu.
 */
function moje_vlastni_styly() {
    // Získání cesty k adresáři šablony
    $theme_dir_uri = get_stylesheet_directory_uri();

    // Načtení stylů pro spodní lištu
    wp_enqueue_style( 
        'moje-mobilni-spodni-lista', 
        $theme_dir_uri . '/css/mobile-bottom-bar.css', 
        array(), 
        '1.0.0' 
    );
}
// Připojení naší funkce k "háčku" WordPressu, který se stará o načítání skriptů a stylů
add_action( 'wp_enqueue_scripts', 'moje_vlastni_styly' );