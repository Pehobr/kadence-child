<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//======================================================================
// 7. KONTROLA PŘÍSTUPU POMOCÍ HESLA A E-MAILU
//======================================================================

function knihaslova_start_session() {
    if ( ! session_id() && ! headers_sent() ) {
        session_start();
    }
}
add_action( 'init', 'knihaslova_start_session', 1 );

/**
 * Zpracuje odeslaný formulář s e-mailem a heslem.
 * Logika je upravena pro zpracování jednoho řetězce odděleného středníky.
 */
function knihaslova_handle_password_form_login_page() {
    if ( isset( $_POST['knihaslova_password_submit'] ) && isset( $_POST['access_email'] ) && isset( $_POST['access_password'] ) ) {
        $submitted_email = sanitize_email( trim( $_POST['access_email'] ) );
        $submitted_password = trim( $_POST['access_password'] );

        $credentials_raw = get_option( 'knihaslova_priest_credentials', '' );
        
        // ZMĚNA: Rozdělení řetězce podle středníku (;) na jednotlivé páry "email,heslo"
        $credentials_pairs = explode( ";", $credentials_raw );
        
        $valid_credential = false;

        // Procházení jednotlivých párů
        foreach ( $credentials_pairs as $pair ) {
            $pair = trim($pair);
            if (empty($pair)) continue;

            // Rozdělení páru na e-mail a heslo pomocí čárky
            $parts = str_getcsv($pair); 
            if (count($parts) === 2) {
                $saved_email = trim($parts[0]);
                $saved_password = trim($parts[1]);

                if ( strtolower( $saved_email ) === strtolower( $submitted_email ) && $submitted_password === $saved_password ) {
                    $valid_credential = true;
                    break; 
                }
            }
        }

        if ( $valid_credential ) {
            $_SESSION['knihaslova_access_granted'] = true;
            $_SESSION['knihaslova_logged_in_email'] = $submitted_email;
            $_SESSION['knihaslova_success_message'] = 'Přihlášení proběhlo úspěšně.';
            unset( $_SESSION['knihaslova_access_error'] );

            // Odeslání notifikačního e-mailu
            $subject = 'Potvrzení o přihlášení na web Kniha Slova';
            $message = "Dobrý den,\n\n"
                     . "zaznamenali jsme úspěšné přihlášení k neveřejnému obsahu na webu Kniha Slova (knihaslova.pehonet.eu) pomocí Vašeho e-mailu.\n\n"
                     . "Datum a čas přihlášení: " . current_time('mysql') . "\n\n"
                     . "Pokud jste se nepřihlašovali Vy, doporučujeme co nejdříve kontaktovat správce webu.\n\n"
                     . "S pozdravem,\nTým Kniha Slova";
            $headers = ['Content-Type: text/plain; charset=UTF-8'];
            
            wp_mail( $submitted_email, $subject, $message, $headers );

        } else {
            $_SESSION['knihaslova_access_error'] = 'Nesprávný e-mail nebo heslo. Zkuste to prosím znovu.';
        }

        wp_redirect( $_SERVER['REQUEST_URI'] );
        exit;
    }
}

function knihaslova_handle_logout() {
    if ( isset($_GET['action']) && $_GET['action'] == 'knihaslova_logout' ) {
        if ( !isset( $_GET['_wpnonce'] ) || !wp_verify_nonce( $_GET['_wpnonce'], 'knihaslova_logout_nonce' ) ) {
            wp_die('Bezpečnostní kontrola selhala.', 'Chyba');
        }

        unset($_SESSION['knihaslova_access_granted']);
        unset($_SESSION['knihaslova_logged_in_email']);
        unset($_SESSION['knihaslova_success_message']);
        $_SESSION['knihaslova_logout_message'] = 'Byli jste úspěšně odhlášeni.';
        
        wp_redirect( get_permalink() );
        exit;
    }
}

function knihaslova_is_access_granted() {
    return isset( $_SESSION['knihaslova_access_granted'] ) && $_SESSION['knihaslova_access_granted'] === true;
}

function knihaslova_display_password_form() {
    ?>
    <div class="password-form-container">
        <div class="password-form-wrapper">
            <h2><i class="fa fa-lock" aria-hidden="true"></i> Obsah pro kněze</h2>
            <p>Pro odemčení neveřejných záložek na stránkách příběhů zadejte prosím Váš e-mail a přístupové heslo.</p>

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
                    <label for="access_email">Váš e-mail:</label>
                    <input type="email" id="access_email" name="access_email" required>
                </div>
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

function knihaslova_display_success_message() {
    $logout_url = wp_nonce_url( add_query_arg('action', 'knihaslova_logout'), 'knihaslova_logout_nonce' );
    $user_email = isset($_SESSION['knihaslova_logged_in_email']) ? esc_html($_SESSION['knihaslova_logged_in_email']) : '';
    ?>
    <div class="password-form-container">
        <div class="password-form-wrapper">
            <h2><i class="fa fa-check-circle" aria-hidden="true"></i> Přihlášení</h2>
            
            <?php
            if ( isset( $_SESSION['knihaslova_success_message'] ) ) {
                echo '<p class="password-success">' . esc_html( $_SESSION['knihaslova_success_message'] ) . '</p>';
                unset($_SESSION['knihaslova_success_message']);
            }
            ?>
            <p>Nyní můžete procházet neveřejný obsah určený pro kněze.</p>
            <a href="<?php echo esc_url($logout_url); ?>" class="button logout-button">Odhlásit se</a>
        </div>
    </div>
    <?php
}

if ( ! function_exists( 'is_user_priest' ) ) {
    /**
     * Kontroluje, zda je aktuální uživatel přihlášen a má roli "kněz".
     *
     * @return bool True, pokud je uživatel kněz, jinak false.
     */
    function is_user_priest() {
        // Získáme aktuálně přihlášeného uživatele
        $user = wp_get_current_user();

        // Zkontrolujeme, zda uživatel existuje a zda má v seznamu svých rolí roli 'knez'
        // 'knez' je zde myšleno jako interní název (slug) role. Ujistěte se, že odpovídá tomu,
        // jak je role vytvořena v administraci WordPressu.
        if ( $user && in_array( 'knez', (array) $user->roles ) ) {
            return true;
        }

        // Pokud podmínky nejsou splněny, vrátíme false
        return false;
    }
}