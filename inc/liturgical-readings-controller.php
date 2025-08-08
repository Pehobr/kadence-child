<?php
/**
 * Controller for handling Liturgical Readings functionality.
 *
 * This file manages routing, data retrieval, and template loading for the
 * liturgical readings feature.
 *
 * @package pehobr
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Adds custom rewrite rules for the liturgical readings.
 * This creates a user-friendly URL structure: /cteni/{sunday_id}/{reading_type}/
 */
function pehobr_add_readings_rewrite_rules() {
    $page_id = get_option('pehobr_reading_detail_page_id');
    if ($page_id) {
        add_rewrite_rule(
            '^cteni/([^/]+)/([12])/?$',
            'index.php?page_id=' . $page_id . '&sunday_id=$matches[1]&reading_type=$matches[2]',
            'top'
        );
    }
}
add_action( 'init', 'pehobr_add_readings_rewrite_rules' );

/**
 * Adds custom query variables to be recognized by WordPress.
 *
 * @param array $vars The array of existing query variables.
 * @return array The modified array of query variables.
 */
function pehobr_add_readings_query_vars( $vars ) {
    $vars[] = 'sunday_id';
    $vars[] = 'reading_type';
    return $vars;
}
add_filter( 'query_vars', 'pehobr_add_readings_query_vars' );

/**
 * Loads the custom template for the liturgical reading detail page.
 *
 * @param string $template The path of the template to include.
 * @return string The path of the new template if the query vars are set.
 */
function pehobr_load_reading_template( $template ) {
    $page_id = get_option('pehobr_reading_detail_page_id');
    if ( $page_id && is_page($page_id) && get_query_var( 'sunday_id' ) ) {
        $new_template = locate_template( array( 'template-cteni.php' ) );
        if ( '' != $new_template ) {
            return $new_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'pehobr_load_reading_template', 99 );

/**
 * Saves the ID of the "Detail Čtení" page to the options table.
 */
function pehobr_save_reading_detail_page_id() {
    $reading_page = get_page_by_path( 'cteni-detail' );
    if ( $reading_page ) {
        update_option( 'pehobr_reading_detail_page_id', $reading_page->ID );
    }
}
add_action( 'after_setup_theme', 'pehobr_save_reading_detail_page_id' );

/**
 * Retrieves all relevant data for a specific reading from the Google Sheet data.
 *
 * @return array|null An associative array with reading data or null if not found.
 */
function get_current_reading_data() {
    $sunday_id = get_query_var( 'sunday_id' );
    $reading_type = get_query_var( 'reading_type' );

    if ( ! $sunday_id || ! $reading_type ) {
        return null;
    }

    $sunday_data = knihaslova_get_sunday_data_by_id( $sunday_id );

    if ( ! $sunday_data ) {
        return null;
    }

    $data = [
        'sunday_id' => $sunday_id,
        'sunday_name' => isset($sunday_data['Nazev_Nedele']) ? $sunday_data['Nazev_Nedele'] : '',
        'liturgical_period' => isset($sunday_data['Obdobi']) ? $sunday_data['Obdobi'] : '',
        'reading_type' => $reading_type,
    ];

    if ( $reading_type == 1 ) {
        $data['citation'] = isset($sunday_data['1_cteni_citace']) ? $sunday_data['1_cteni_citace'] : '';
        $data['text'] = isset($sunday_data['1_cteni_text']) ? $sunday_data['1_cteni_text'] : '';
        $data['title'] = 'První čtení';
    } elseif ( $reading_type == 2 ) {
        $data['citation'] = isset($sunday_data['2_cteni_citace']) ? $sunday_data['2_cteni_citace'] : '';
        $data['text'] = isset($sunday_data['2_cteni_text']) ? $sunday_data['2_cteni_text'] : '';
        $data['title'] = 'Druhé čtení';
    } else {
        return null;
    }
    
    if ( empty($data['citation']) && empty($data['text']) ) {
        return null;
    }

    return $data;
}

/**
 * Najde a vrátí příspěvek typu "Nedělní čtení" na základě ID neděle a typu čtení.
 *
 * @param string $sunday_id ID neděle (např. 'mezidobi-19').
 * @param string $reading_type Typ čtení ('1' nebo '2').
 * @return WP_Post|null Objekt příspěvku nebo null, pokud není nalezen.
 */
function knihaslova_get_reading_commentary( $sunday_id, $reading_type ) {
    if ( empty( $sunday_id ) || empty( $reading_type ) ) {
        return null;
    }

    $args = array(
        'post_type'      => 'nedelni_cteni', // Změněno z 'vyklad'
        'posts_per_page' => 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'prirazeni_k_nedeli',
                'value'   => $sunday_id,
                'compare' => '=',
            ),
            array(
                'key'     => 'typ_cteni',
                'value'   => $reading_type,
                'compare' => '=',
            ),
        ),
    );

    $commentary_query = new WP_Query( $args );

    if ( $commentary_query->have_posts() ) {
        return $commentary_query->posts[0];
    }

    return null;
}
