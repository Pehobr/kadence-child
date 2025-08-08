<?php
/*
 * Template Name: Přihlašovací stránka
 * Description: Šablona pro stránku s formulářem pro zadání hesla.
 */

// Zpracujeme případné odeslání formuláře (přihlášení)
knihaslova_handle_password_form_login_page();

// Zpracujeme případný požadavek na odhlášení
knihaslova_handle_logout();

get_header();
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <?php
            // Zkontrolujeme, zda je uživatel přihlášený
            if ( knihaslova_is_access_granted() ) {
                // Pokud ano, zobrazíme zprávu o úspěchu a tlačítko pro odhlášení
                knihaslova_display_success_message();
            } else {
                // Pokud ne, zobrazíme klasický přihlašovací formulář
                knihaslova_display_password_form();
            }
            ?>
        </main>
        <?php get_sidebar(); ?>
    </div>
</div>

<?php
get_footer();