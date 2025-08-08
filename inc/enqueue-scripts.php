<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//======================================================================
// 3. NAČÍTÁNÍ VLASTNÍCH CSS A JS SOUBORŮ
//======================================================================

function child_theme_configurator_css() {
    // Načte základní styl child šablony
    wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'kadence-global','kadence-header','kadence-content','kadence-footer' ) );

    // Načtení stylů pro mobilní prvky
    wp_enqueue_style( 'moje-mobilni-hlavicka', get_stylesheet_directory_uri() . '/css/mobile-header.css', array('chld_thm_cfg_child'), '1.0.0' );
    wp_enqueue_style( 'moje-mobilni-spodni-lista', get_stylesheet_directory_uri() . '/css/mobile-bottom-bar.css', array('chld_thm_cfg_child'), '1.0.0' );

    // Podmíněné načítání stylů pro specifické stránky a šablony
    if ( is_singular('evangelijni_pribeh') ) {
        wp_enqueue_style( 'kniha-slova-single-styles', get_stylesheet_directory_uri() . '/css/single-pribehu.css', array('chld_thm_cfg_child'), '1.0.2' );
    } 
    elseif ( is_page_template('template-cteni.php') ) {
        wp_enqueue_style( 'kniha-slova-single-cteni-styles', get_stylesheet_directory_uri() . '/css/single-cteni.css', array('chld_thm_cfg_child'), '1.0.0' );
    }
    elseif ( is_post_type_archive('evangelijni_pribeh') || is_page_template('template-pribehy.php') ) {
        wp_enqueue_style( 'kniha-slova-archive-styles', get_stylesheet_directory_uri() . '/css/archiv-pribehu.css', array('chld_thm_cfg_child'), '1.0.1' );
    } elseif ( is_page_template('page-katalog.php') ) {
        wp_enqueue_style( 'kniha-slova-katalog-styles', get_stylesheet_directory_uri() . '/css/katalog.css', array('chld_thm_cfg_child'), '1.0.0' );
    } elseif ( is_page_template('template-vyhledavani-citaci.php') ) {
        wp_enqueue_style( 'kniha-slova-vyhledavani-citaci-styles', get_stylesheet_directory_uri() . '/css/vyhledavani-citaci.css', array('chld_thm_cfg_child'), '1.0.0' );
    } elseif ( is_page_template('page-liturgicky-rok.php') ) {
        wp_enqueue_style( 'knihaslova-liturgicky-rok-style', get_stylesheet_directory_uri() . '/css/page-liturgicky-rok.css', array('chld_thm_cfg_child'), filemtime(get_stylesheet_directory() . '/css/page-liturgicky-rok.css') );
    }

    // Styly pro doplňky (tlačítka, pod-záložky atd.)
    if (
        is_page_template('page-katalog.php') ||
        is_page_template('page-liturgicky-rok.php') ||
        is_page_template('template-cteni.php')
    ) {
        wp_enqueue_style( 'pehobr-liturgical-reading', get_stylesheet_directory_uri() . '/css/liturgical-reading.css', array('chld_thm_cfg_child'), '1.0.1' );
    }

    // Načtení Font Awesome
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );
}
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 20 );

function knihaslova_enqueue_scripts() {
    // Globální JS
    wp_enqueue_script( 'kniha-slova-global-js', get_stylesheet_directory_uri() . '/js/global.js', array(), '1.0.0', true );

    // JS pro evangelijní příběhy
    if ( is_singular('evangelijni_pribeh') || is_post_type_archive('evangelijni_pribeh') || is_page_template('template-pribehy.php') ) {
        wp_enqueue_script( 'kniha-slova-main-js', get_stylesheet_directory_uri() . '/js/main.js', array('jquery'), '1.0.5', true );
    }
    
    // JS pro katalog
    if ( is_page_template('page-katalog.php') ) {
        wp_enqueue_script( 'kniha-slova-katalog-js', get_stylesheet_directory_uri() . '/js/katalog.js', array(), '1.0.0', true );
    }

    // JS pro detail nedělního čtení
    if ( is_page_template('template-cteni.php') ) {
        wp_enqueue_script( 'kniha-slova-cteni-js', get_stylesheet_directory_uri() . '/js/cteni.js', array('jquery'), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'knihaslova_enqueue_scripts' );
