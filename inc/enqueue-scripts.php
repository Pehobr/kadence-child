<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//======================================================================
// 3. NAČÍTÁNÍ VLASTNÍCH CSS A JS SOUBORŮ
//======================================================================

function child_theme_configurator_css() {
    // Načte základní styl child šablony
    wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'kadence-global','kadence-header','kadence-content','kadence-footer' ) );

    // Načtení stylů pro mobilní hlavičku (fixní pozice atd.)
    wp_enqueue_style( 'moje-mobilni-hlavicka', get_stylesheet_directory_uri() . '/css/mobile-header.css', array('chld_thm_cfg_child'), '1.0.0' );

    // Načtení stylů pro spodní mobilní lištu
    wp_enqueue_style( 'moje-mobilni-spodni-lista', get_stylesheet_directory_uri() . '/css/mobile-bottom-bar.css', array('chld_thm_cfg_child'), '1.0.0' );

    // Podmíněné načítání stylů podle typu stránky
    if ( is_singular('evangelijni_pribeh') ) {
        // Načte styly pouze pro detail příběhu
        wp_enqueue_style( 'kniha-slova-single-styles', get_stylesheet_directory_uri() . '/css/single-pribehu.css', array('chld_thm_cfg_child'), '1.0.1' );
    } elseif ( is_post_type_archive('evangelijni_pribeh') || is_page_template('template-pribehy.php') ) {
        // Načte styly pouze pro archiv příběhů
        wp_enqueue_style( 'kniha-slova-archive-styles', get_stylesheet_directory_uri() . '/css/archiv-pribehu.css', array('chld_thm_cfg_child'), '1.0.1' );
    } elseif ( is_page_template('page-katalog.php') ) {
        // Načte styly pouze pro stránku katalogu
        wp_enqueue_style( 'kniha-slova-katalog-styles', get_stylesheet_directory_uri() . '/css/katalog.css', array('chld_thm_cfg_child'), '1.0.0' );
    }

    // Správné načtení knihovny ikon Font Awesome
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );
}
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 20 );

function knihaslova_enqueue_scripts() {
    // Načítání JS souboru na detailu, na archivu i na hlavní stránce příběhů
    if ( is_singular('evangelijni_pribeh') || is_post_type_archive('evangelijni_pribeh') || is_page_template('template-pribehy.php') ) {
        wp_enqueue_script( 'kniha-slova-main-js', get_stylesheet_directory_uri() . '/js/main.js', array('jquery'), '1.0.5', true );
    }
    
    // Načítání JS souboru pro stránku katalogu
    if ( is_page_template('page-katalog.php') ) {
        wp_enqueue_script( 'kniha-slova-katalog-js', get_stylesheet_directory_uri() . '/js/katalog.js', array(), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'knihaslova_enqueue_scripts' );