<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

//======================================================================
// 5. VÝVOJÁŘSKÁ SEKCE PRO SPRÁVU WEBU
//======================================================================

/**
 * Přidá do administrace novou položku hlavního menu "Admin".
 */
function knihaslova_add_admin_menu() {
    add_menu_page(
        'Admin Nastavení', // Název stránky (title)
        'Admin',           // Text v menu
        'manage_options',  // Oprávnění pro zobrazení
        'kniha_slova_admin', // Slug stránky
        'knihaslova_admin_page_html', // Funkce pro vykreslení obsahu
        'dashicons-admin-generic', // Ikona
        2 // Pozice v menu
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
    </div>
    <?php
}

/**
 * Registruje nastavení, sekce a pole pro admin stránku.
 */
function knihaslova_settings_init() {
    // Sekce pro vývojáře
    register_setting('knihaslova_admin_settings', 'knihaslova_dev_options');
    add_settings_section(
        'knihaslova_dev_section',
        'Vývojářské nástroje',
        'knihaslova_dev_section_cb',
        'kniha_slova_admin'
    );
    add_settings_field(
        'force_clear_transients',
        'Vynutit smazání cache',
        'knihaslova_force_clear_transients_cb',
        'kniha_slova_admin',
        'knihaslova_dev_section'
    );

    // NOVÁ SEKCE PRO HESLA
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

// Callbacky pro sekci vývojářů
function knihaslova_dev_section_cb($args) {
    ?>
    <p id="<?php echo esc_attr($args['id']); ?>">Zde můžete spravovat pokročilé funkce webu.</p>
    <?php
}

function knihaslova_force_clear_transients_cb() {
    $options = get_option('knihaslova_dev_options');
    $checked = isset($options['force_clear_transients']) ? $options['force_clear_transients'] : 'off';
    ?>
    <label for="force_clear_transients">
        <input type="checkbox"
               id="force_clear_transients"
               name="knihaslova_dev_options[force_clear_transients]"
               value="on"
               <?php checked('on', $checked); ?>
        >
        Aktivovat (Při každém načtení stránky příběhu se smaže její dočasná mezipaměť a data se stáhnou znovu z Google Sheets).
    </label>
    <p class="description">
        <strong>Varování:</strong> Tuto volbu zapínejte pouze dočasně pro vývoj a ladění. Ponechání této volby aktivní může výrazně zpomalit web.
    </p>
    <?php
}

// NOVÉ CALLBACKY PRO SEKCI HESEL
function knihaslova_passwords_section_cb($args) {
    ?>
    <p id="<?php echo esc_attr($args['id']); ?>">Zde můžete spravovat hesla, která odemknou skrytý obsah na webu.</p>
    <?php
}

function knihaslova_access_passwords_field_cb() {
    $passwords = get_option('knihaslova_passwords', '');
    ?>
    <textarea name="knihaslova_passwords" id="access_passwords_field" rows="10" cols="50" class="large-text"><?php echo esc_textarea($passwords); ?></textarea>
    <p class="description">
        Zadejte jedno heslo na každý řádek. Uživatel se bude moci přihlásit kterýmkoli z uvedených hesel.
    </p>
    <?php
}