<?php // $Revision: 1.7 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: login.php,v 1.7 2004/12/14 23:20:37 pixtur Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$checkSession = false;
require_once('../includes/library.php');
$pageSection = 'login';

// Request values are optional on the initial visit. Keep local, typed defaults
// instead of relying on the old register_globals behaviour.
$logout = $_GET['logout'] ?? '';
$sessionStatus = $_GET['session'] ?? '';
$sessionReason = $_GET['reason'] ?? '';
$redirectUrl = $_GET['url'] ?? ($_POST['url'] ?? '');
$loginSubmit = $_POST['loginSubmit'] ?? '';
$loginForm = $_POST['loginForm'] ?? '';
$passwordForm = $_POST['passwordForm'] ?? '';
$rememberForm = $_POST['rememberForm'] ?? '';
$languageForm = $_POST['languageForm'] ?? ($langDefault ?? '');
$auth = 'off';

// DEBUG
// foreach ($_POST as $k => $v) { print "<font color=blue>\$_POST[$k] => $v</font><br>"; }
// foreach ($_GET as $k => $v) { print "<font color=green>\$_GET[$k] => $v</font><br>"; }
// foreach ($_SESSION as $k => $v) { print "<font color=red>\$_SESSION[$k] => $v</font><br>"; }
// foreach ($_COOKIE as $k => $v) { print "<font color=purple>\$_COOKIE[$k] => $v</font><br>"; }
// foreach ($_SERVER as $k => $v) { print "<font color=purple>\$_SERVER[$k] => $v</font><br>"; }

if (($logout == 'true') and (isset($_SESSION['loginSession']))) {
    // update the logs table before logout
    $tmpquery1 = 'UPDATE ' . $tableCollab['logs'] . ' SET connected=NULL ';
    $tmpquery1 .= 'WHERE login="' . $_SESSION['loginSession'] . '"';
    connectSql($tmpquery1);

    // delete the authentication cookies
    setcookie('NetOfficeAuthCookie', '', time() - 86400, $base_uri);

    // handle the session
    $_SESSION = array(); // unset all session varables
    session_unset();
    _sess_mysql_destroy(session_id()); // then destroy the session

    // redirection to login page with logout message
    header('Location: ../general/login.php?msg=logout');
    exit;
}

$match = false;
$ssl = false;
// if (!empty($SSL_CLIENT_CERT) && !$_GET['logout'] && $_GET['loginSubmit']) {
// $auth = 'on';
// $ssl = true;

// if (function_exists('openssl_x509_read')) {
// $x509 = openssl_x509_read($SSL_CLIENT_CERT);
// $cert_array = openssl_x509_parse($x509, true);
// $subject_array = $cert_array['subject'];
// $ssl_email = $subject_array['Email'];
// openssl_x509_free($x509);
// } else {
// $ssl_email = `echo "$SSL_CLIENT_CERT" | $pathToOpenssl x509 -noout -email`;
// }
// } else {
// test blank fields in form
if ($loginSubmit != '') {
    if ($loginForm == '' and $passwordForm == '') {
        $error = $strings['login_username'] . '<br>' . $strings['login_password'];
    } else if ($loginForm == '') {
        $error = $strings['login_username'];
    } else if ($passwordForm == '') {
        $error = $strings['login_password'];
    } else {
        $auth = 'on';
        if ($rememberForm != 'on') {
            setcookie('NetOfficeAuthCookie', '', time() - 3600, $base_uri);
        }
    }
}

if ($forcedLogin == 'false') {
    if (($auth == 'on') and ($loginForm == '') and ($passwordForm == '')) {
        $auth = 'off';
        $error = 'Detecting variables poisoning ;-)';
    }
}
// }

// Get cookie params. A missing or malformed cookie simply means that the user
// must log in normally.
$authCookie = array();
$encodedAuthCookie = $_COOKIE['NetOfficeAuthCookie'] ?? '';
if ($encodedAuthCookie != '') {
    $decodedAuthCookie = base64_decode($encodedAuthCookie, true);
    if ($decodedAuthCookie !== false) {
        $cookieData = @unserialize($decodedAuthCookie, array('allowed_classes' => false));
        if (is_array($cookieData)) {
            $authCookie = $cookieData;
        }
    }
}
$loginCookie = $authCookie['loginForm'] ?? '';
$passwordCookie = $authCookie['storePwd'] ?? '';
$tokenCookie = $authCookie['tokenSession'] ?? '';

// A stale remember-me cookie must never override credentials that the user
// has just typed into the form.
if ($loginSubmit != '') {
    $loginCookie = '';
    $passwordCookie = '';
    $tokenCookie = '';
}

if ($loginCookie != '' && $passwordCookie != '' && $tokenCookie != '') {
    $auth = 'on';
}

if ($auth == 'on') {
    $loginForm = trim(strip_tags($loginForm));
    // Passwords are opaque values. Sanitizing them changes valid passwords
    // containing characters that resemble HTML and guarantees a mismatch.
    $passwordForm = (string) $passwordForm;

    if ($loginCookie != '' && $passwordCookie != '' && $tokenCookie != '') {
        $loginForm = $loginCookie;
    }

    // Authentication must depend only on the members table. The generic
    // member query joins organizations and logs, even though neither belongs
    // in credential validation and logs may intentionally have been cleared.
    $loginConnection = openDatabase();
    $memberTable = str_replace('`', '``', $tableCollab['members']);
    $loginColumn = $ssl ? 'email_work' : 'login';
    $loginLookup = $ssl ? $ssl_email : $loginForm;
    $demoCondition = $demoMode == true ? '' : " AND login != 'demo'";
    $loginStatement = mysqli_prepare(
        $loginConnection,
        "SELECT id, login, password, name, profil, logout_time, last_page, timezone " .
        "FROM `$memberTable` WHERE `$loginColumn` = ?$demoCondition AND profil != '4' LIMIT 1"
    );
    $loginUser = new stdClass();
    $comptLoginUser = 0;

    if ($loginStatement) {
        mysqli_stmt_bind_param($loginStatement, 's', $loginLookup);
        mysqli_stmt_execute($loginStatement);
        mysqli_stmt_bind_result(
            $loginStatement,
            $memberId,
            $memberLogin,
            $memberPassword,
            $memberName,
            $memberProfile,
            $memberLogoutTime,
            $memberLastPage,
            $memberTimezone
        );
        if (mysqli_stmt_fetch($loginStatement)) {
            $loginUser->mem_id = array($memberId);
            $loginUser->mem_login = array($memberLogin);
            $loginUser->mem_password = array($memberPassword);
            $loginUser->mem_name = array($memberName);
            $loginUser->mem_profil = array($memberProfile);
            $loginUser->mem_logout_time = array($memberLogoutTime);
            $loginUser->mem_last_page = array($memberLastPage);
            $loginUser->mem_timezone = array($memberTimezone);
            $comptLoginUser = 1;
        }
        mysqli_stmt_close($loginStatement);
    } else {
        $error = 'Login database query failed: ' . htmlspecialchars(mysqli_error($loginConnection));
    }

    // test if user exits
    if ($comptLoginUser == '0') {
        if (empty($error)) {
            $error = 'No active internal user was found with this exact user name.';
        }
        setcookie('NetOfficeAuthCookie', '', time() - 3600, $base_uri);
    } else {
        // test password
        if ($loginCookie != '' && $passwordCookie != '' && $tokenCookie != '') {
            if (!$ssl && $passwordCookie != $loginUser->mem_password[0]) {
                $error = $strings['invalid_login'];
                setcookie('NetOfficeAuthCookie', '', time() - 3600, $base_uri);
            } else {
                // password passed, now test token
                if (!$ssl && $tokenCookie != md5($loginCookie . $cryptKey)) {
                    $error = $strings['invalid_login'];
                    setcookie('NetOfficeAuthCookie', '', time() - 3600, $base_uri);
                } else {
                    $match = true;
                }
            }
        } else {

            if ((!is_password_match($loginForm, $passwordForm, $loginUser->mem_password[0]))) {
                $storedPassword = (string) $loginUser->mem_password[0];
                if (password_get_info($storedPassword)['algo'] !== null) {
                    $storedFormat = 'secure password hash';
                } else if (preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
                    $storedFormat = 'MD5';
                } else if (strlen($storedPassword) === 13 || substr($storedPassword, 0, 1) === '$') {
                    $storedFormat = 'CRYPT';
                } else {
                    $storedFormat = 'plain/unknown';
                }
                $error = 'The user exists, but the password comparison failed. ' .
                    'Stored password format: ' . $storedFormat . '.';
            } else {
                $match = true;
            }
        }

        if ($match == true) {
            $sessionPassword = (string) $loginUser->mem_password[0];

            // Upgrade legacy hashes after a successful password-based login.
            if ($loginCookie == '' &&
                (password_get_info($sessionPassword)['algo'] === null || password_needs_rehash($sessionPassword, PASSWORD_DEFAULT))) {
                $sessionPassword = get_password($passwordForm);
                $passwordUpdate = mysqli_prepare($loginConnection, "UPDATE `$memberTable` SET password = ? WHERE id = ?");
                if (!$passwordUpdate) {
                    exit('Unable to prepare password security upgrade.');
                }
                mysqli_stmt_bind_param($passwordUpdate, 'si', $sessionPassword, $memberId);
                if (!mysqli_stmt_execute($passwordUpdate)) {
                    exit('Unable to upgrade password security.');
                }
                mysqli_stmt_close($passwordUpdate);
            }

            if ($loginCookie == '' && $rememberForm == 'on') {
                $cookieValue = base64_encode(serialize(array(
                    'loginForm' => $loginForm,
                    'storePwd' => $sessionPassword,
                    'tokenSession' => md5($loginForm . $cryptKey),
                )));
                setcookie('NetOfficeAuthCookie', $cookieValue, time() + 31536000, $base_uri);
            }

            // get the ip addr
            $ip = SESS_REMOTE_ADDR;

            // set session variables
            $_SESSION['browserSession'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['idSession'] = $loginUser->mem_id[0];
            $_SESSION['timezoneSession'] = $loginUser->mem_timezone[0];
            $_SESSION['languageSession'] = $languageForm;
            $_SESSION['loginSession'] = $loginForm;
            $_SESSION['passwordSession'] = $sessionPassword;
            $_SESSION['nameSession'] = $loginUser->mem_name[0];
            $_SESSION['ipSession'] = $ip;
            $_SESSION['dateunixSession'] = date('U');
            $_SESSION['dateSession'] = date('d-m-Y H:i:s');
            $_SESSION['profilSession'] = $loginUser->mem_profil[0];
            $_SESSION['logouttimeSession'] = $loginUser->mem_logout_time[0];
            $_SESSION['tokenSession'] = md5($loginForm . $cryptKey);

            // register demo session = true in session if user = demo
            if ($loginForm == 'demo') {
                $demoSession = true;
                $_SESSION['demoSession'] = $demoSession;
            }

            // Keep exactly one login-log row for the current session. Older
            // code could create duplicate rows and later validate an obsolete
            // session depending on which row MySQL returned first.
            $session = session_id();
            $logConnection = openDatabase();
            $logTable = str_replace('`', '``', $tableCollab['logs']);
            $deleteLog = mysqli_prepare($logConnection, "DELETE FROM `$logTable` WHERE login = ?");
            $insertLog = mysqli_prepare(
                $logConnection,
                "INSERT INTO `$logTable` (login,password,ip,session,compt,last_visite) VALUES (?,?,?,?,1,?)"
            );

            if (!$deleteLog || !$insertLog) {
                exit('Unable to prepare login session record.');
            }

            mysqli_stmt_bind_param($deleteLog, 's', $loginForm);
            mysqli_stmt_execute($deleteLog);
            mysqli_stmt_close($deleteLog);

            mysqli_stmt_bind_param($insertLog, 'sssss', $loginForm, $sessionPassword, $ip, $session, $dateheure);
            if (!mysqli_stmt_execute($insertLog)) {
                exit('Unable to save login session record.');
            }
            mysqli_stmt_close($insertLog);
            // redirect for external link to internal page
            if ($redirectUrl != '') {
                if ($loginUser->mem_profil[0] == '3') {
                    header('Location: ../' . $redirectUrl . '&updateProject=true');
                    exit;
                } else {
                    header('Location: ../' . $redirectUrl);
                    exit;
                }
            } else if (($loginUser->mem_last_page[0] != '') and ($loginUser->mem_profil[0] != '3')) {
                // redirect to selected start page
                header('Location: ../' . $loginUser->mem_last_page[0]);
                exit;
                // } else if ($loginUser->mem_last_page[0] != '' && ($loginCookie != '' && $passwordCookie != '' && $tokenCookie != '') && $loginUser->mem_profil[0] != '3') {
                // $tmpquery = 'UPDATE '.$tableCollab['members']." SET last_page='' WHERE login = '$loginForm'";
                // connectSql($tmpquery);
                // header('Location: ../'.$loginUser->mem_last_page[0]);
                // exit;
            } else {
                // redirect to home or admin page (if user is administrator)
                if ($loginUser->mem_profil[0] == '3') {
                    header('Location: ../projects_site/home.php');
                    exit;
                } else if ($loginUser->mem_profil[0] == '0') {
                    header('Location: ../administration/admin.php');
                    exit;
                } else {
                    header('Location: ../general/home.php');
                    exit;
                }
            }
        }
    }
}

if (($sessionStatus == 'false') and ($redirectUrl == '')) {
    $sessionReasons = array(
        'session_cookie_missing' => 'The browser did not send the session cookie. It may have expired, been cleared, or be blocked.',
        'session_data_missing' => 'The session cookie exists, but its server-side session data is missing or expired.',
        'idle_timeout' => 'The session expired because it was inactive longer than your configured logout time.',
        'token_invalid' => 'The session security token is invalid. The encryption key may have changed, or the session data was modified.',
        'log_row_missing' => 'The active login record is missing. This happens when the Logs page deletes the row used to validate your session.',
        'session_replaced' => 'This account was signed in from another session, so this session is no longer current.',
        'session_registry_error' => 'The session registry could not be queried because of a database or table error.',
        'client_area_restricted' => 'This client account tried to open an area that is restricted to internal users.',
    );
    $error = $strings['session_false'];
    if (isset($sessionReasons[$sessionReason])) {
        $error .= '<br>' . $sessionReasons[$sessionReason];
    } else {
        $error .= '<br>The session could not be validated. Please log in again.';
    }
}

if ($logout == 'true') {
    $msg = 'logout';
}

if ($demoMode == true) {
    $loginForm = 'demo';
    $passwordForm = 'demo';
}

$notLogged = true;
$bodyCommand = 'onLoad="document.loginForm.loginForm.focus();"';


//---- header ---------------------------
require_once('../themes/' . THEME . '/header.php');


//------- content ----------------------------------------------------
?>
<style>
    body {
        background: #eef3f7;
    }

    .sidebar,
    .mobile-menu-toggle {
        display: none;
    }

    .content {
        margin-left: 0 !important;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    #footer {
        width: min(980px, calc(100% - 32px));
        margin: 14px auto 24px;
        padding: 0;
        color: #6b7787;
        background: transparent;
    }

    #footer .site-footer__main {
        background: transparent;
        border: 0;
        box-shadow: none;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 12px;
        justify-content: initial;
        padding: 0 4px;
    }

    #footer .site-footer__brand {
        margin-right: 0;
    }

    #footer .site-footer__link {
        text-decoration: none;
    }

    #footer .site-footer__chip {
        background: rgba(255, 255, 255, 0.72);
    }

    .login-page {
        width: 100%;
        flex: 1;
        min-height: auto;
        display: grid;
        place-items: center;
        padding: 32px 16px 0;
        color: #162033;
    }

    .login-page,
    .login-page div,
    .login-page section {
        overflow: visible;
    }

    .login-shell {
        width: min(980px, 100%);
        display: grid;
        grid-template-columns: minmax(280px, 0.9fr) minmax(320px, 1fr);
        background: #ffffff;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        box-shadow: 0 24px 70px rgba(34, 49, 72, 0.14);
        overflow: hidden;
    }

    .login-brand {
        position: relative;
        padding: 42px;
        background:
            linear-gradient(135deg, rgba(22, 71, 115, 0.92), rgba(34, 101, 95, 0.9)),
            url('../themes/deepblue/bg_main.gif');
        background-size: auto, 320px 320px;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 520px;
    }

    .login-brand__mark {
        width: 68px;
        height: 68px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        margin-bottom: 28px;
    }

    .login-brand__mark img {
        max-width: 50px;
        max-height: 50px;
    }

    .login-brand h1 {
        font-size: 2.45rem;
        line-height: 1.04;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0 0 16px;
    }

    .login-brand p {
        color: rgba(255, 255, 255, 0.82);
        font-size: 1rem;
        margin: 0;
        max-width: 30rem;
    }

    .login-brand__meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 30px;
    }

    .login-brand__meta span {
        border: 1px solid rgba(255, 255, 255, 0.26);
        border-radius: 999px;
        color: rgba(255, 255, 255, 0.86);
        font-size: 0.78rem;
        padding: 6px 10px;
    }

    .login-panel {
        padding: 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-panel__header {
        margin-bottom: 28px;
    }

    .login-panel__eyebrow {
        color: #2f6f6a;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .login-panel h2 {
        color: #162033;
        font-size: 1.85rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .login-panel .form-label {
        color: #334155;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .login-panel .form-control,
    .login-panel .form-select {
        min-height: 46px;
        border-color: #cbd6e2;
        border-radius: 8px;
        color: #162033;
    }

    .login-panel .form-control:focus,
    .login-panel .form-select:focus {
        border-color: #2f6f6a;
        box-shadow: 0 0 0 0.2rem rgba(47, 111, 106, 0.18);
    }

    .login-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 4px 0 24px;
    }

    .login-submit {
        min-height: 48px;
        border: 0;
        border-radius: 8px;
        background: #164773;
        color: #ffffff;
        font-weight: 700;
        padding: 0 18px;
        transition: background-color 0.2s ease, transform 0.2s ease;
        width: 100%;
    }

    .login-submit:hover,
    .login-submit:focus {
        background: #0f3558;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .login-forgot {
        text-align: center;
        margin-top: 20px;
    }

    .login-forgot a {
        color: #164773;
        font-weight: 700;
        text-decoration: none;
    }

    .login-forgot a:hover {
        text-decoration: underline;
    }

    .login-error {
        border-radius: 8px;
        margin-bottom: 24px;
    }

    @media (max-width: 992px) {
        .login-page {
            place-items: start center;
            padding-top: 72px;
        }

        .login-shell {
            grid-template-columns: 1fr;
        }

        .login-brand {
            min-height: auto;
            padding: 32px;
        }

        .login-brand__meta {
            margin-top: 24px;
        }
    }

    @media (max-width: 560px) {
        .login-page {
            padding: 64px 10px 24px;
        }

        .login-brand,
        .login-panel {
            padding: 26px 20px;
        }

        .login-brand h1 {
            font-size: 2rem;
        }

        .login-actions {
            align-items: flex-start;
            flex-direction: column;
        }

        #footer {
            width: calc(100% - 20px);
        }

        #footer .site-footer__main {
            grid-template-columns: 1fr;
            justify-items: center;
            text-align: center;
        }
    }
</style>

<div class="login-page">
    <div class="login-shell">
        <section class="login-brand" aria-label="NetOffice">
            <div>
                <div class="login-brand__mark">
                    <img src="<?php echo htmlspecialchars($base_uri . 'themes/deepblue/img/logo-sidebar.png'); ?>" alt="TaskVibe">
                </div>
                <h1>TaskVibe</h1>
                <p><?php echo $strings['please_login']; ?></p>
            </div>
            <div class="login-brand__meta" aria-hidden="true">
                <span><?php echo $strings['projects']; ?></span>
                <span><?php echo $strings['calendar']; ?></span>
                <span><?php echo $strings['reports']; ?></span>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-panel__header">
                <div class="login-panel__eyebrow"><?php echo $strings['login']; ?></div>
                <h2><?php echo $strings['please_login']; ?></h2>
            </div>

            <?php if ($error != '') { ?>
                <div class="alert alert-danger login-error" role="alert">
                    <strong><?php echo $strings['errors']; ?></strong><br>
                    <?php echo $error; ?>
                </div>
            <?php } ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" name="loginForm">
                <?php
                if ($redirectUrl != '') {
                    echo '<input value="' . htmlspecialchars($redirectUrl) . '" type="hidden" name="url">';
                }
                ?>

                <div class="mb-3">
                    <label for="languageForm" class="form-label"><?php echo $strings['language']; ?></label>
                    <select name="languageForm" id="languageForm" class="form-select">
                        <?php
                        array_multisort($langValue, SORT_ASC, SORT_STRING);
                        foreach ($langValue as $key => $value) {
                            if (file_exists('../languages/lang_' . $key . '.php')) {
                                $selected = ($langDefault == $key) ? 'selected' : '';
                                $display = ($langDefault == $key) ? "$value (Default)" : $value;
                                echo '<option value="' . $key . '" ' . $selected . '>' . htmlspecialchars($display) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="loginForm" class="form-label">* <?php echo $strings['user_name']; ?></label>
                    <input type="text" name="loginForm" id="loginForm" value="<?php echo htmlspecialchars($loginForm); ?>" class="form-control" autocomplete="username">
                </div>

                <div class="mb-3">
                    <label for="passwordForm" class="form-label">* <?php echo $strings['password']; ?></label>
                    <input type="password" name="passwordForm" id="passwordForm" value="<?php echo htmlspecialchars($passwordForm); ?>" class="form-control" autocomplete="current-password">
                </div>

                <div class="login-actions">
                    <div class="form-check">
                        <input type="checkbox" name="rememberForm" value="on" id="rememberForm" class="form-check-input">
                        <label class="form-check-label" for="rememberForm"><?php echo $strings['remember_password']; ?></label>
                    </div>
                </div>

                <input type="submit" name="loginSubmit" class="login-submit" value="<?php echo $strings['login']; ?>">

                <div class="login-forgot">
                    <?php echo buildLink('../general/sendpassword.php', $strings['forgot_pwd'], 'in'); ?>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    (function() {
        var login = document.getElementById('loginForm');
        if (login) {
            login.focus();
        }
    }());
</script>

<?php

require_once('../themes/' . THEME . '/footer.php');

?>
