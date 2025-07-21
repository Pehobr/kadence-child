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

// 7. Kontrola přístupu chráněného heslem
require_once $inc_dir . 'access-control.php';

/**
 * Načte speciální CSS soubor pouze pro přihlašovací stránku.
 */
function knihaslova_enqueue_login_styles() {
    // Zkontrolujeme, zda se jedná o stránku, která používá naši přihlašovací šablonu
    if ( is_page_template('template-login.php') ) {
        // Načteme CSS soubor
        wp_enqueue_style(
            'kniha-slova-login-style', // Unikátní název stylu
            get_stylesheet_directory_uri() . '/css/login-style.css', // Cesta k souboru
            array(), // Případné závislosti (nejsou potřeba)
            '1.0.0' // Verze souboru
        );
    }
}
// Připojíme naši funkci k háku 'wp_enqueue_scripts'
add_action( 'wp_enqueue_scripts', 'knihaslova_enqueue_login_styles' );

/**
 * Změní výchozí řazení stránek v administraci WordPressu.
 * Stránky budou seřazeny podle data vytvoření (od nejnovějších).
 */
function knihaslova_zmenit_razeni_stranek_v_adminu( $query ) {
    // Ujistíme se, že jsme v administraci, jedná se o hlavní dotaz
    // a že jsme na stránce se seznamem stránek ('page').
    if ( is_admin() && $query->is_main_query() && $query->get('post_type') === 'page' ) {
        
        // Zkontrolujeme, zda není řazení již nastaveno uživatelem (kliknutím na sloupec)
        if ( ! isset( $_GET['orderby'] ) ) {
            // Nastavíme řazení podle data
            $query->set( 'orderby', 'date' );
            
            // Nastavíme směr řazení na sestupný (nejnovější nahoře)
            $query->set( 'order', 'DESC' );
        }
    }
}
// Připojíme naši funkci k háku 'pre_get_posts'
add_action( 'pre_get_posts', 'knihaslova_zmenit_razeni_stranek_v_adminu' );