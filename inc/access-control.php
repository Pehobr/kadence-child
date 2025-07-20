<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//======================================================================
// 7. KONTROLA PŘÍSTUPU POMOCÍ HESLA (finální verze s čárkami)
//======================================================================

/**
 * Spustí PHP session, pokud ještě neběží.
 */
function knihaslova_start_session() {
    if ( ! session_id() && ! headers_sent() ) {
        session_start();
    }
}
add_action( 'init', 'knihaslova_start_session', 1 );

/**
 * Zpracuje odeslaný formulář s heslem na přihlašovací stránce.
 */
function knihaslova_handle_password_form_login_page() {
    if ( isset( $_POST['knihaslova_password_submit'] ) && isset( $_POST['access_password'] ) ) {
        $saved_passwords_raw = get_option( 'knihaslova_passwords', '' );
        // --- ZMĚNA ZDE: Hesla nyní rozdělujeme podle ČÁRKY ---
        $saved_passwords = array_map( 'trim', explode( ",", $saved_passwords_raw ) );
        $submitted_password = trim( $_POST['access_password'] );

        if ( in_array( $submitted_password, $saved_passwords ) && !empty($submitted_password) ) {
            $_SESSION['knihaslova_access_granted'] = true;
            $_SESSION['knihaslova_success_message'] = 'Přihlášení proběhlo úspěšně.';
            unset( $_SESSION['knihaslova_access_error'] );
        } else {
            $_SESSION['knihaslova_access_error'] = 'Nesprávné heslo. Zkuste to prosím znovu.';
        }

        wp_redirect( $_SERVER['REQUEST_URI'] );
        exit;
    }
}

/**
 * Zpracuje požadavek na ODHLÁŠENÍ.
 */
function knihaslova_handle_logout() {
    if ( isset($_GET['action']) && $_GET['action'] == 'knihaslova_logout' ) {
        if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'knihaslova_logout_nonce' ) ) {
            wp_die('Bezpečnostní kontrola selhala.', 'Chyba');
        }

        unset($_SESSION['knihaslova_access_granted']);
        unset($_SESSION['knihaslova_success_message']);
        $_SESSION['knihaslova_logout_message'] = 'Byli jste úspěšně odhlášeni.';
        
        wp_redirect( get_permalink() );
        exit;
    }
}

/**
 * Zkontroluje, zda má uživatel platný přístup.
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
            <h2><i class="fa fa-lock" aria-hidden="true"></i> Obsah pro kněze</h2>
            <p>Pro odemčení neveřejných záložek na stránkách příběhů zadejte prosím přístupové heslo.</p>

            <?php
            if ( isset( $_SESSION['knihaslova_access_error'] ) ) {
                echo '<p class="password-error">' . esc_html( $_SESSION['knihaslova_access_error'] ) . '</p>';
                unset( $_SESSION['knihaslova_access_error'] );
            }
            if ( isset( $_SESSION['knihaslova_logout_message'] ) ) {
                echo '<p class="password-success">' . esc_html( $_SESSION['knihaslova_logout_message'] ) . '</p>';
                unset( $_SESSION['knihaslova_logout_message'] );
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

/**
 * Vykreslí zprávu o úspěšném přihlášení a tlačítko pro odhlášení.
 */
function knihaslova_display_success_message() {
    $logout_url = wp_nonce_url( add_query_arg('action', 'knihaslova_logout'), 'knihaslova_logout_nonce' );
    ?>
    <div class="password-form-container">
        <div class="password-form-wrapper">
            <h2><i class="fa fa-check-circle" aria-hidden="true"></i> Přihlášení úspěšné</h2>
            
            <?php
            if ( isset( $_SESSION['knihaslova_success_message'] ) ) {
                echo '<p class="password-success">' . esc_html( $_SESSION['knihaslova_success_message'] ) . '</p>';
            }
            ?>
            <p>Nyní můžete procházet neveřejný obsah určený pro kněze.</p>
            <a href="<?php echo esc_url($logout_url); ?>" class="button logout-button">Odhlásit se</a>
        </div>
    </div>
    <?php
}