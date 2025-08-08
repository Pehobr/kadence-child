<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Registruje vlastní typ příspěvku "Evangelijní příběh".
 */
function knihaslova_register_pribeh_cpt() {
    $labels = array(
        'name'                  => _x( 'Evangelijní příběhy', 'Post Type General Name', 'kadence-child' ),
        'singular_name'         => _x( 'Evangelijní příběh', 'Post Type Singular Name', 'kadence-child' ),
        'menu_name'             => __( 'Evangelijní příběhy', 'kadence-child' ),
        'name_admin_bar'        => __( 'Evangelijní příběh', 'kadence-child' ),
        'archives'              => __( 'Archiv příběhů', 'kadence-child' ),
        'all_items'             => __( 'Všechny příběhy', 'kadence-child' ),
        'add_new_item'          => __( 'Přidat nový příběh', 'kadence-child' ),
        'add_new'               => __( 'Přidat nový', 'kadence-child' ),
        'new_item'              => __( 'Nový příběh', 'kadence-child' ),
        'edit_item'             => __( 'Upravit příběh', 'kadence-child' ),
        'update_item'           => __( 'Aktualizovat příběh', 'kadence-child' ),
        'view_item'             => __( 'Zobrazit příběh', 'kadence-child' ),
        'search_items'          => __( 'Hledat v příbězích', 'kadence-child' ),
        'not_found'             => __( 'Nenalezeno', 'kadence-child' ),
        'not_found_in_trash'    => __( 'Nenalezeno v koši', 'kadence-child' ),
    );
    $args = array(
        'label'                 => __( 'Evangelijní příběh', 'kadence-child' ),
        'description'           => __( 'Evangelijní příběhy a jejich výklady.', 'kadence-child' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'author' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-book',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'pribehy-evangelia',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => array('slug' => 'pribeh'),
    );
    register_post_type( 'evangelijni_pribeh', $args );
}
add_action( 'init', 'knihaslova_register_pribeh_cpt', 0 );


/**
 * Registruje vlastní typ příspěvku "Nedělní čtení".
 */
function knihaslova_register_sunday_reading_cpt() {
    $labels = array(
        'name'                  => _x( 'Nedělní čtení', 'Post Type General Name', 'kadence-child' ),
        'singular_name'         => _x( 'Nedělní čtení', 'Post Type Singular Name', 'kadence-child' ),
        'menu_name'             => __( 'Nedělní čtení', 'kadence-child' ),
        'name_admin_bar'        => __( 'Nedělní čtení', 'kadence-child' ),
        'archives'              => __( 'Archiv nedělních čtení', 'kadence-child' ),
        'attributes'            => __( 'Atributy čtení', 'kadence-child' ),
        'parent_item_colon'     => __( 'Nadřazené čtení:', 'kadence-child' ),
        'all_items'             => __( 'Všechna nedělní čtení', 'kadence-child' ),
        'add_new_item'          => __( 'Přidat nové nedělní čtení', 'kadence-child' ),
        'add_new'               => __( 'Přidat nové', 'kadence-child' ),
        'new_item'              => __( 'Nové nedělní čtení', 'kadence-child' ),
        'edit_item'             => __( 'Upravit nedělní čtení', 'kadence-child' ),
        'update_item'           => __( 'Aktualizovat nedělní čtení', 'kadence-child' ),
        'view_item'             => __( 'Zobrazit nedělní čtení', 'kadence-child' ),
        'view_items'            => __( 'Zobrazit nedělní čtení', 'kadence-child' ),
        'search_items'          => __( 'Hledat v nedělních čteních', 'kadence-child' ),
        'not_found'             => __( 'Nenalezeno', 'kadence-child' ),
        'not_found_in_trash'    => __( 'Nenalezeno v koši', 'kadence-child' ),
        'featured_image'        => __( 'Obrázek', 'kadence-child' ),
        'set_featured_image'    => __( 'Nastavit obrázek', 'kadence-child' ),
        'remove_featured_image' => __( 'Odebrat obrázek', 'kadence-child' ),
        'use_featured_image'    => __( 'Použít jako obrázek', 'kadence-child' ),
        'insert_into_item'      => __( 'Vložit do nedělního čtení', 'kadence-child' ),
        'uploaded_to_this_item' => __( 'Nahráno k tomuto čtení', 'kadence-child' ),
        'items_list'            => __( 'Seznam nedělních čtení', 'kadence-child' ),
        'items_list_navigation' => __( 'Navigace seznamu čtení', 'kadence-child' ),
        'filter_items_list'     => __( 'Filtrovat seznam čtení', 'kadence-child' ),
    );
    $args = array(
        'label'                 => __( 'Nedělní čtení', 'kadence-child' ),
        'description'           => __( 'Podklady pro liturgická čtení.', 'kadence-child' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'author' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6, // Posunuto o jednu pozici níže
        'menu_icon'             => 'dashicons-book-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'nedelni-cteni',
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'rewrite'               => array('slug' => 'nedelni-cteni'),
    );
    register_post_type( 'nedelni_cteni', $args );
}
add_action( 'init', 'knihaslova_register_sunday_reading_cpt', 0 );
