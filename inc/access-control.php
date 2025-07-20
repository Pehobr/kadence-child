<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//======================================================================
// 7. KONTROLA PŘÍSTUPU POMOCÍ HESLA
//======================================================================

/**
 * Spustí PHP session, pokud ještě neběží.
 */
function knihaslova_start_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'knihaslova_start_session' );

/**
 * Zpracuje odeslaný formulář s heslem na přihlašovací stránce.
 */
function knihaslova_handle_password_form_login_page() {
    if ( isset( $_POST['knihaslova_password_submit'] ) && isset( $_POST['access_password'] ) ) {
        // Získáme uložená hesla z databáze
        $saved_passwords_raw = get_option( 'knihaslova_passwords', '' );
        $saved_passwords = explode( "\n", $saved_passwords_raw );
        $saved_passwords = array_map( 'trim', $saved_passwords ); // Odstraníme bílé znaky

        $submitted_password = trim( $_POST['access_password'] );

        // Zkontrolujeme, zda zadané heslo existuje v poli uložených hesel
        if ( in_array( $submitted_password, $saved_passwords ) && !empty($submitted_password) ) {
            $_SESSION['knihaslova_access_granted'] = true;
            unset( $_SESSION['knihaslova_access_error'] ); // Smažeme případnou starou chybu

            // Přesměrujeme na archiv příběhů po úspěšném přihlášení
            $redirect_url = get_post_type_archive_link('evangelijni_pribeh');
            if (!$redirect_url) {
                $redirect_url = home_url('/');
            }
            wp_redirect( $redirect_url );
            exit;

        } else {
            // Nastavíme chybovou zprávu
            $_SESSION['knihaslova_access_error'] = 'Nesprávné heslo. Zkuste to prosím znovu.';

            // Přesměrujeme zpět na přihlašovací stránku, abychom zabránili opětovnému odeslání formuláře
            wp_redirect( $_SERVER['REQUEST_URI'] );
            exit;
        }
    }
}

/**
 * Zkontroluje, zda má uživatel platný přístup.
 * @return bool True pokud je přístup povolen, jinak false.
 */
function knihaslova_is_access_granted() {
    return isset( $_SESSION['knihaslova_access_granted'] ) && $_SESSION['knihaslova_access_granted'] === true;
}

/**
 * Vykreslí HTML formulář pro zadání hesla.
 */
function knihaslova_display_password_form() {
    ?>
    <div class="password-form-container">
        <div class="password-form-wrapper">
            <h2><i class="fa fa-lock" aria-hidden="true"></i> Prémiový obsah</h2>
            <p>Pro odemčení prémiových záložek na stránkách příběhů zadejte prosím přístupové heslo.</p>

            <?php
            // Zobrazíme chybovou zprávu, pokud existuje
            if ( isset( $_SESSION['knihaslova_access_error'] ) ) {
                echo '<p class="password-error">' . esc_html( $_SESSION['knihaslova_access_error'] ) . '</p>';
                unset( $_SESSION['knihaslova_access_error'] ); // Smažeme zprávu po zobrazení
            }
            ?>

            <form method="post" action="" class="password-form">
                <div class="form-group">
                    <label for="access_password">Přístupové heslo:</label>
                    <input type="password" id="access_password" name="access_password" required>
                </div>
                <button type="submit" name="knihaslova_password_submit">Odemknout obsah</button>
            </form>
        </div>
    </div>
    <?php
}