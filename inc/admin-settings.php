<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//======================================================================
// 5. VÝVOJÁŘSKÁ SEKCE PRO SPRÁVU WEBU
//======================================================================

/**
 * Přidá do administrace novou položku hlavního menu "Admin".
 */
function knihaslova_add_admin_menu() {
    add_menu_page(
        'Admin Nastavení',
        'Admin',
        'manage_options',
        'kniha_slova_admin',
        'knihaslova_admin_page_html',
        'dashicons-admin-generic',
        2
    );
}
add_action('admin_menu', 'knihaslova_add_admin_menu');

/**
 * Vykreslí obsah stránky "Admin".
 */
function knihaslova_admin_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Zkontroluje, zda bylo stisknuto tlačítko pro manuální aktualizaci
    if (isset($_POST['knihaslova_manual_update_submit'])) {
        // Bezpečnostní kontrola (nonce)
        check_admin_referer('knihaslova_manual_update_nonce');

        // Zavolá funkci pro aktualizaci dat
        $success = knihaslova_manual_data_update();

        // Zobrazí zprávu o výsledku
        if ($success) {
            echo '<div class="notice notice-success is-dismissible"><p>Data z Google Sheets byla úspěšně načtena a uložena.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Při načítání dat z Google Sheets došlo k chybě. Zkontrolujte chybové logy serveru pro více informací.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form action="options.php" method="post">
            <?php
            settings_fields('knihaslova_admin_settings');
            do_settings_sections('kniha_slova_admin');
            submit_button('Uložit nastavení');
            ?>
        </form>

        <hr> 

        <form method="post" action="">
            <h2>Manuální aktualizace dat</h2>
            <p>Kliknutím na tlačítko níže smažete stávající uložená data a nahrajete nová, aktuální data z Google Sheets. Tento proces může trvat několik sekund.</p>
            <p><strong>Použijte toto tlačítko vždy, když přidáte nebo upravíte příběh v Google tabulce.</strong></p>
            <?php
            // Přidání bezpečnostního klíče (nonce)
            wp_nonce_field('knihaslova_manual_update_nonce');
            ?>
            <input type="submit" name="knihaslova_manual_update_submit" id="knihaslova_manual_update_submit" class="button button-primary" value="Načíst a uložit data z Google Sheets">
        </form>
    </div>
    <?php
}

/**
 * Registruje nastavení, sekce a pole pro admin stránku.
 */
function knihaslova_settings_init() {
    // Sekce pro vývojáře byla odebrána, protože již není potřeba
    
    // Sekce pro hesla
    register_setting('knihaslova_admin_settings', 'knihaslova_passwords');
    add_settings_section(
        'knihaslova_passwords_section',
        'Přístupová hesla',
        'knihaslova_passwords_section_cb',
        'kniha_slova_admin'
    );
    add_settings_field(
        'access_passwords_field',
        'Hesla pro přístup',
        'knihaslova_access_passwords_field_cb',
        'kniha_slova_admin',
        'knihaslova_passwords_section'
    );
}
add_action('admin_init', 'knihaslova_settings_init');

// Callbacky pro sekci hesel
function knihaslova_passwords_section_cb($args) {
    echo '<p id="' . esc_attr($args['id']) . '">Zde můžete spravovat hesla, která odemknou neveřejný obsah na webu.</p>';
}

function knihaslova_access_passwords_field_cb() {
    $passwords = get_option('knihaslova_passwords', '');
    ?>
    <input type="text" name="knihaslova_passwords" id="access_passwords_field" value="<?php echo esc_attr($passwords); ?>" class="large-text">
    <p class="description">
        Zadejte jedno nebo více hesel oddělených čárkou (např. heslo1,heslo2,dalsiheslo).
    </p>
    <?php
}