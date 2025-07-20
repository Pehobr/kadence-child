<?php
/*
 * Template Name: Přihlašovací stránka
 * Description: Šablona pro stránku s formulářem pro zadání hesla.
 */

// Zpracujeme odeslání formuláře PŘED načtením hlavičky, abychom mohli přesměrovat
knihaslova_handle_password_form_login_page();

get_header();
?>

<div id="primary" class="content-area">
    <div class="content-container site-container">
        <main id="main" class="site-main">
            <?php
            // Zobrazíme přihlašovací formulář
            knihaslova_display_password_form();
            ?>
        </main>
    </div>
</div>

<?php
get_footer();