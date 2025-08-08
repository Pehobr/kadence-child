<?php
/**
 * Vlastní úpravy a funkce pro child šablonu Kniha Slova.
 *
 * @package Kadence Child
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registrace vlastních umístění pro menu.
 */
function kniha_slova_register_nav_menus() {
	register_nav_menus( array(
		'leve_mobilni_menu'  => __( 'Levé mobilní menu', 'kadence-child' ),
		'prave_mobilni_menu' => __( 'Pravé mobilní menu', 'kadence-child' ), // PŘIDÁNO: Registrace pravého menu
	) );
}
add_action( 'init', 'kniha_slova_register_nav_menus' );


/**
 * Přidá vlastní CSS do hlavičky na základě nastavení v administraci.
 * Konkrétně pro skrytí ikony mobilního menu.
 */
function knihaslova_custom_styles_in_header() {
    // Zkontrolujeme, zda je zaškrtnuta volba pro skrytí ikony
    if ( get_option('knihaslova_hide_mobile_menu_icon') ) {
        // OPRAVA: Použijeme správný selektor #mobile-toggle pro ID prvku
        $custom_css = "
            #mobile-toggle {
                display: none !important;
            }
        ";
        // Vložíme styl do hlavičky stránky
        echo '<style type="text/css">' . $custom_css . '</style>';
    }
}
add_action( 'wp_head', 'knihaslova_custom_styles_in_header' );