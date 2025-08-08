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

    // Podpoložka pro nastavení liturgického roku
    add_submenu_page(
        'knihaslova_farar',         // Rodičovský slug (menu "Farář")
        'Liturgický Rok',           // Titulek stránky
        'Liturgický Rok',           // Název v menu
        'manage_options',           // Oprávnění
        'knihaslova_liturgy_settings', // Slug podstránky
        'knihaslova_liturgy_settings_page_html' // Funkce pro vykreslení obsahu
    );

    // --- NOVÉ: Podpoložka pro nastavení zobrazení ---
    add_submenu_page(
        'knihaslova_farar',
        'Nastavení zobrazení',
        'Zobrazení',
        'manage_options',
        'knihaslova_display_settings',
        'knihaslova_display_settings_page_html'
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
 * Vykreslí obsah stránky pro nastavení liturgického roku.
 */
function knihaslova_liturgy_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('knihaslova_liturgy_settings');
            do_settings_sections('knihaslova_liturgy_settings');
            submit_button('Uložit nastavení');
            ?>
        </form>
    </div>
    <?php
}

/**
 * --- NOVÉ: Vykreslí obsah stránky pro nastavení zobrazení ---
 */
function knihaslova_display_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('knihaslova_display_settings');
            do_settings_sections('knihaslova_display_settings');
            submit_button('Uložit nastavení zobrazení');
            ?>
        </form>
    </div>
    <?php
}

/**
 * Registruje nastavení, sekce a pole pro stránku s hesly, liturgií a zobrazením.
 */
function knihaslova_settings_init() {
    // --- Nastavení pro hesla ---
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

    // --- Nastavení pro liturgický rok ---
    register_setting('knihaslova_liturgy_settings', 'knihaslova_override_reference_date');
    register_setting('knihaslova_liturgy_settings', 'knihaslova_override_reference_sunday_id');
    register_setting('knihaslova_liturgy_settings', 'knihaslova_liturgical_cycle_base_year_A');

    add_settings_section(
        'knihaslova_liturgy_override_section',
        'Manuální kalibrace liturgického roku',
        'knihaslova_liturgy_override_section_cb',
        'knihaslova_liturgy_settings'
    );
    add_settings_field(
        'override_reference_date_field',
        'Referenční datum',
        'knihaslova_override_reference_date_field_cb',
        'knihaslova_liturgy_settings',
        'knihaslova_liturgy_override_section'
    );
    add_settings_field(
        'override_reference_sunday_id_field',
        'Referenční neděle',
        'knihaslova_override_reference_sunday_id_field_cb',
        'knihaslova_liturgy_settings',
        'knihaslova_liturgy_override_section'
    );
     add_settings_field(
        'liturgical_cycle_base_year_A_field',
        'Základní rok pro cyklus A',
        'knihaslova_liturgical_cycle_base_year_A_field_cb',
        'knihaslova_liturgy_settings',
        'knihaslova_liturgy_override_section'
    );

    // --- NOVÉ: Nastavení pro zobrazení ---
    register_setting('knihaslova_display_settings', 'knihaslova_hide_mobile_menu_icon');
    add_settings_section(
        'knihaslova_mobile_display_section',
        'Mobilní zobrazení',
        'knihaslova_mobile_display_section_cb',
        'knihaslova_display_settings'
    );
    add_settings_field(
        'hide_mobile_menu_icon_field',
        'Skrýt ikonu menu v hlavičce',
        'knihaslova_hide_mobile_menu_icon_field_cb',
        'knihaslova_display_settings',
        'knihaslova_mobile_display_section'
    );
}
add_action('admin_init', 'knihaslova_settings_init');

function knihaslova_hesla_section_cb($args) {
    echo '<p id="' . esc_attr($args['id']) . '">Zde spravujete přihlašovací údaje (e-mail a heslo), které odemknou neveřejný obsah na webu.</p>';
}

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

function knihaslova_liturgy_override_section_cb($args) {
    echo '<p id="' . esc_attr($args['id']) . '">Tato nastavení slouží k manuálnímu "ukotvení" data konkrétní neděle, aby se ostatní neděle v roce dopočítaly správně. To je užitečné zejména pro neděle v mezidobí po Seslání Ducha Svatého.</p>';
    echo '<p><strong>Jak to funguje:</strong> Zadejte datum a vyberte, která neděle z vašeho seznamu na toto datum připadá. Systém pak dopočítá data ostatních neděl v týdenních intervalech od tohoto bodu.</p>';
}

function knihaslova_override_reference_date_field_cb() {
    $date = get_option('knihaslova_override_reference_date', '');
    ?>
    <input type="date" name="knihaslova_override_reference_date" id="override_reference_date_field" value="<?php echo esc_attr($date); ?>">
    <p class="description">Zadejte datum, které bude sloužit jako referenční bod.</p>
    <?php
}

function knihaslova_override_reference_sunday_id_field_cb() {
    $selected_id = get_option('knihaslova_override_reference_sunday_id', '');
    $all_sundays = get_option('knihaslova_liturgical_year_data');

    if (empty($all_sundays)) {
        echo '<p>Nejdříve prosím načtěte data z Google Sheets na hlavní stránce "Farář".</p>';
        return;
    }
    ?>
    <select name="knihaslova_override_reference_sunday_id" id="override_reference_sunday_id_field" class="large-text">
        <option value="">-- Vyberte neděli --</option>
        <?php foreach ($all_sundays as $sunday): ?>
            <?php if (!empty($sunday['ID_Nedele']) && !empty($sunday['Nazev_Nedele'])): ?>
                <option value="<?php echo esc_attr($sunday['ID_Nedele']); ?>" <?php selected($selected_id, $sunday['ID_Nedele']); ?>>
                    <?php echo esc_html($sunday['Nazev_Nedele'] . " (" . $sunday['Obdobi'] . " - " . $sunday['Cyklus'] . ")"); ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
    <p class="description">Vyberte neděli, která odpovídá zadanému referenčnímu datu.</p>
    <?php
}

function knihaslova_liturgical_cycle_base_year_A_field_cb() {
    $base_year = get_option('knihaslova_liturgical_cycle_base_year_A', '2023');
    ?>
    <input type="number" name="knihaslova_liturgical_cycle_base_year_A" id="liturgical_cycle_base_year_A_field" value="<?php echo esc_attr($base_year); ?>" placeholder="Např. 2023">
    <p class="description">Zadejte rok, kdy začíná cyklus A (např. 2023, který začal adventem 2022). Slouží pro automatický výpočet, pokud není aktivní manuální kalibrace.</p>
    <?php
}

// --- NOVÉ: Callbacky pro sekci a pole nastavení zobrazení ---

function knihaslova_mobile_display_section_cb($args) {
    echo '<p id="' . esc_attr($args['id']) . '">Nastavení viditelnosti jednotlivých prvků na mobilních zařízeních.</p>';
}

function knihaslova_hide_mobile_menu_icon_field_cb() {
    $option = get_option('knihaslova_hide_mobile_menu_icon');
    ?>
    <input type="checkbox" name="knihaslova_hide_mobile_menu_icon" id="hide_mobile_menu_icon_field" value="1" <?php checked($option, 1); ?>>
    <label for="hide_mobile_menu_icon_field">Ano, skrýt ikonu mobilního menu.</label>
    <p class="description">
        Zaškrtněte, pokud chcete v hlavičce na mobilních zařízeních skrýt ikonu pro otevření postranního menu (hamburger).
    </p>
    <?php
}