<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//======================================================================
// 5. VÝVOJÁŘSKÁ SEKCE PRO SPRÁVU WEBU (přejmenováno na "Farář")
//======================================================================

/**
 * Přidá do administrace novou hlavní položku menu "Farář" a její podpoložky.
 */
function knihaslova_add_admin_menu() {
    // Hlavní položka menu "Farář"
    add_menu_page(
        'Farář - Nástroje',         // Titulek stránky v prohlížeči
        'Farář',                    // Název v menu
        'manage_options',           // Požadovaná oprávnění
        'knihaslova_farar',         // Slug (unikátní identifikátor) menu
        'knihaslova_farar_main_page_html', // Funkce pro vykreslení obsahu hlavní stránky
        'dashicons-admin-users',    // Ikona (ikona pro uživatele)
        3                           // Pozice v menu
    );

    // Podpoložka menu "Hesla farností"
    add_submenu_page(
        'knihaslova_farar',         // Rodičovský slug (menu "Farář")
        'Hesla farností',           // Titulek stránky v prohlížeči
        'Hesla farností',           // Název v menu
        'manage_options',           // Požadovaná oprávnění
        'knihaslova_hesla',         // Slug této podstránky
        'knihaslova_hesla_page_html' // Funkce pro vykreslení obsahu stránky s hesly
    );
}
add_action('admin_menu', 'knihaslova_add_admin_menu');

/**
 * Vykreslí obsah hlavní stránky "Farář" (pro manuální aktualizaci).
 */
function knihaslova_farar_main_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['knihaslova_manual_update_submit']) && check_admin_referer('knihaslova_manual_update_nonce')) {
        $success = knihaslova_manual_data_update();
        if ($success) {
            echo '<div class="notice notice-success is-dismissible"><p>Data z Google Sheets byla úspěšně načtena a uložena.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Při načítání dat z Google Sheets došlo k chybě. Zkontrolujte chybové logy serveru pro více informací.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>Nástroje pro správu obsahu webu Kniha Slova.</p>
        
        <hr> 

        <form method="post" action="">
            <h2>Manuální aktualizace dat</h2>
            <p>Kliknutím na tlačítko níže smažete stávající uložená data a nahrajete nová, aktuální data z Google Sheets. Tento proces může trvat několik sekund.</p>
            <p><strong>Použijte toto tlačítko vždy, když přidáte nebo upravíte příběh v Google tabulce.</strong></p>
            <?php
            wp_nonce_field('knihaslova_manual_update_nonce');
            ?>
            <input type="submit" name="knihaslova_manual_update_submit" id="knihaslova_manual_update_submit" class="button button-primary" value="Načíst a uložit data z Google Sheets">
        </form>
    </div>
    <?php
}

/**
 * Vykreslí obsah stránky pro správu hesel farností.
 */
function knihaslova_hesla_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('knihaslova_hesla_settings');
            do_settings_sections('knihaslova_hesla');
            submit_button('Uložit hesla');
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registruje nastavení, sekce a pole pro stránku s hesly.
 */
function knihaslova_settings_init() {
    register_setting('knihaslova_hesla_settings', 'knihaslova_priest_credentials');

    add_settings_section(
        'knihaslova_hesla_section',
        'Správa přístupových údajů',
        'knihaslova_hesla_section_cb',
        'knihaslova_hesla'
    );

    add_settings_field(
        'priest_credentials_field',
        'Přihlašovací údaje',
        'knihaslova_priest_credentials_field_cb',
        'knihaslova_hesla',
        'knihaslova_hesla_section'
    );
}
add_action('admin_init', 'knihaslova_settings_init');

function knihaslova_hesla_section_cb($args) {
    echo '<p id="' . esc_attr($args['id']) . '">Zde spravujete přihlašovací údaje (e-mail a heslo), které odemknou neveřejný obsah na webu.</p>';
}

/**
 * Callback pro vykreslení pole s přihlašovacími údaji.
 */
function knihaslova_priest_credentials_field_cb() {
    $credentials = get_option('knihaslova_priest_credentials', '');
    ?>
    <textarea name="knihaslova_priest_credentials" id="priest_credentials_field" class="large-text" rows="10"><?php echo esc_textarea($credentials); ?></textarea>
    <p class="description">
        Zadejte přihlašovací údaje ve formátu: <strong>e-mail,heslo</strong>. Jednotlivé páry oddělte středníkem (<strong>;</strong>).
        <br>
        Příklad: <strong>farni.email@domena.cz,Heslo123;dalsi.email@farnost.cz,DalsiHeslo456</strong>
    </p>
    <?php
}