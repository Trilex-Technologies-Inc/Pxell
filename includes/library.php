<?php // $Revision: 1.18 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: library.php,v 1.18 2005/06/12 15:45:37 madbear Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

/*
Some notes: (pixtur 2004-11-11)
This is a collection of global functions that is included at the beginning of
most other pages. Referres to login, if '$checkSession'==true and not session
open.

ToDo:
- get rid of extract();
- predefine global variables
- either fix demo-user or depreciate incomplete code
- define constants for MEMBER_TYPES (sessionProfil)

*/
error_reporting(E_ALL & ~E_NOTICE);


//---!!! hack for fixing session-header problem on win32 ---
ob_start(); // output buffering

//======================================================================================
// global variables
//======================================================================================
//NOTE:
//	- this list is incomplete, but newly introduced globals should be listed here
//	- some are commented since we can be sure they are defined BEFORE library
$pageSection='';			// current section in TopNavigation (eg 'home','projects', etc.)
//$_SESSION['profilSession']=='';		// member-type (0=admin, 1=...)
$breadcrumbs=array();	// breads for current page as html-fragments. Must be filled before including "header.php".



//--- defining link-type-constants----
define('LINK_INSIDE', 	'in');
define('LINK_STRIKE',	'in_strike');
define('LINK_BLANK',	'in_blank');
define('LINK_OUT',		'out');
define('LINK_ICON',		'icone');
define('LINK_POWERED',	'powered');
define('LINK_MAIL',		'mail');

//--- all available sections with url (this should be a list of objects...)
$headerSections=array(
	'login'=>		'../general/login.php',
	'requirements'=>'../general/systemrequirements.php',
	'license'=>		'../general/license.php',
	'home'=>		'../general/home.php',
	'projects'=>	'../projects/listprojects.php',
	'clients'=>		'../clients/listclients.php',
	'reports'=>		'../reports/createreport.php',
	'search'=>		'../search/createsearch.php',
	'calendar'=>	'../calendar/viewcalendar.php',
	'bookmarks'=>	'../bookmarks/listbookmarks.php?view=all',
	'preferences'=>	'../preferences/updateuser.php',
	'admin'=>	    '../administration/admin.php'
);

// start the benchmark
$parse_start = getmicrotime();

// set base directory, change all back slashes to forward slashes on Windows
$base_dir = str_replace('\\', '/', dirname(dirname(__FILE__)) . '/');

// load the custom error handler
require_once($base_dir . 'includes/error_handler.php');

// Redirect every application entry point to setup when the generated
// configuration is absent or clearly incomplete. index.php already had a
// similar check, but direct URLs such as general/login.php previously failed
// with a fatal require error instead.
$settingsFile = $base_dir . 'includes/settings.php';
$installerFile = $base_dir . 'installation/setup.php';
$settingsMissing = !is_file($settingsFile) || !is_readable($settingsFile) || filesize($settingsFile) <= 1024;

if ($settingsMissing && is_file($installerFile)) {
    $scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $relativeScript = ltrim(str_replace($base_dir, '', $scriptFilename), '/');
    $scriptDepth = dirname($relativeScript) === '.' ? 0 : substr_count(dirname($relativeScript), '/') + 1;
    header('Location: ' . str_repeat('../', $scriptDepth) . 'installation/setup.php');
    exit;
}

require_once($settingsFile);

// A partially written settings file can exist and still lack the values the
// application requires. Treat it as an incomplete installation as well.
$settingsComplete = defined('MYSERVER') && defined('MYLOGIN') &&
    defined('MYPASSWORD') && defined('MYDATABASE') &&
    isset($root, $databaseType, $tableCollab) && is_array($tableCollab);
if (!$settingsComplete && is_file($installerFile)) {
    $scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $relativeScript = ltrim(str_replace($base_dir, '', $scriptFilename), '/');
    $scriptDepth = dirname($relativeScript) === '.' ? 0 : substr_count(dirname($relativeScript), '/') + 1;
    header('Location: ' . str_repeat('../', $scriptDepth) . 'installation/setup.php');
    exit;
}

// set base URI
$url_array = parse_url($root);
$base_uri = $url_array['path'] . '/';

// PHP 8.3 is the supported runtime baseline.
// if you take this out don't even think about asking for help with bugs!!
if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    header('Location: ' . $base_uri . 'general/error.php?type=phpversion');
    exit;
}

// Session Settings
ini_set('session.use_trans_sid', 0); 		// Stop adding SID to URLs
ini_set('session.serialize_handler', 'php');// How to store data
ini_set('session.use_cookies', 1); 			// Cookies store the session ID
ini_set('session.cookie_path', $base_uri); 	// session cookie save path

// Require session identifiers to be transported only via cookies.
ini_set('session.use_only_cookies', 1);

// set the cache pages expire time in minutes
session_cache_expire(180);

// more session settings
ini_set('session.name', 'netOfficeSID'); // Name of the cookie
ini_set('session.gc_probability', 1); // Garbage Collection
ini_set('session.gc_maxlifetime', 3600); // max session time
ini_set('session.cookie_lifetime', 0); // cookie last browser session
ini_set('session.auto_start', 0); // don't auto start sessions

// how long a session can be inactive
define('SESS_MAXLIFE', 3600);

// {nocache|private|publuc|private_no_cache}
session_cache_limiter('none');

// if we should check IPADDR along with SESSID for enhanced security?
define('SESS_IPCHECK', true);

// get the remote IPADDR
define('SESS_REMOTE_ADDR', get_remote_addr());

// database link needs to be initialized
$MY_DBH = null;

// make use of persistent database connections?
define('DB_PCONNECT', false);

// user call-back functions for session events
session_set_save_handler('_sess_mysql_open', '_sess_mysql_close',
                         '_sess_mysql_read', '_sess_mysql_write',
                         '_sess_mysql_destroy', '_sess_mysql_gc');

// Kick it, lets get this party started
session_start();

// load request data class
require_once($base_dir . 'includes/initrequests.php');
require_once($base_dir . 'includes/request.class.php');

// load theme layout class
require_once($base_dir . 'themes/' . THEME . '/block.class.php');

// register globals work around, I don't like this and am working
// to get rid of it...
if (ini_get('register_globals') == false) {
    // GET array
    if (!empty($_GET)) {
        extract($_GET);
    }

    // POST array
    if (!empty($_POST)) {
        extract($_POST);
    }
}

// languages array
$langValue = array(
    'en'         => 'English',
    'fr'         => 'French',
    'de'         => 'German',
    'it'         => 'Italian',
    'es'         => 'Spanish',
    'pt'         => 'Portuguese',
    'da'         => 'Danish',
    'no'         => 'Norwegian',
    'nl'         => 'Dutch',
    'zh'         => 'Chinese simplified',
    'uk'         => 'Ukrainian',
    'pl'         => 'Polish',
    'in'         => 'Indonesian',
    'ru'         => 'Russian',
    'az'         => 'Azerbaijani',
    'ko'         => 'Korean',
    'zh-tw'      => 'Chinese traditional',
    'ca'         => 'Catalan',
    'pt-br'      => 'Brazilian Portuguese',
    'et'         => 'Estonian',
    'bg'         => 'Bulgarian',
    'ro'         => 'Romanian',
    'hu'         => 'Hungarian',
    'cs-iso'     => 'Czech (iso)',
    'cs-win1250' => 'Czech (win1250)',
    'is'         => 'Icelandic',
    'sk-win1250' => 'Slovak (win1250)',
    'sv'         => 'Swedish',
    'tr'         => 'Turkish',
    'lv'         => 'Latvian',
    'jp'         => 'Japanese'
    );

if ($langDefault != '') {
    $langSelected[$langDefault] = 'selected';
}
else {
    $langSelected = '';
}

// language browser detection
if ($langDefault == '') {
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if ($acceptLang !== '') {
        $plng = explode(',', $acceptLang);
        if (count($plng) > 0) {
            foreach ($plng as $v) {
                $k = explode(';', $v, 2);
                //$k = explode('-', $k[0]);	// removed - it disallows locale variations
                // does the language file exists and is it in the array?
                if (@file_exists('../languages/lang_' . $k[0] . '.php') &&
                        array_key_exists($k[0], $langValue)) {
                    $langDefault = $k[0];
                    break;
                }
                $langDefault = 'en';
            }
        } else {
            $langDefault = 'en';
        }
    } else {
        $langDefault = 'en';
    }
}

// check session validity, except for demo user
if ($checkSession && !$demoSession) {
    // Keep the reason machine-readable so the login page can explain why the
    // session was rejected. These values contain no credentials or IDs.
    $rejectSession = static function ($reason) use ($base_uri) {
        header('Location: ' . $base_uri . 'general/login.php?session=false&reason=' . rawurlencode($reason));
        exit;
    };

    if (empty($_SESSION['loginSession']) || empty($_SESSION['tokenSession'])) {
        $rejectSession(isset($_COOKIE[session_name()]) ? 'session_data_missing' : 'session_cookie_missing');
    }

    // a client user trying to get outside of the "client project site"
    if (($_SESSION['profilSession'] ?? null) == 3 && !strstr($_SERVER['PHP_SELF'], 'projects_site')) {
        $rejectSession('client_area_restricted');
    }

    // if auto logout feature used, check the idle time
    if (($_SESSION['profilSession'] ?? null) != '3') {
        if (!empty($_SESSION['logouttimeSession'])) {
            $dateunix = date('U');
            $diff = $dateunix - $_SESSION['dateunixSession'];

            if ($diff > $_SESSION['logouttimeSession']) {
                $rejectSession('idle_timeout');
            } else {
                // update the last activity time in the session
                $_SESSION['dateunixSession'] = $dateunix;
            }
        }
    }

    // verify they have logged in, if not redirect to the login page
    if ($_SESSION['tokenSession'] != md5($_SESSION['loginSession'] . $cryptKey)) {
        $rejectSession('token_invalid');
    }

    // Validate the exact login/session pair. Do not use the generic logs
    // wrapper or rely on the first row when legacy duplicate rows exist.
    $logConnection = openDatabase();
    $logTable = str_replace('`', '``', $tableCollab['logs']);
    $checkLog = mysqli_prepare(
        $logConnection,
        "SELECT 1 FROM `$logTable` WHERE login = ? AND session = ? LIMIT 1"
    );
    $loginSession = $_SESSION['loginSession'];
    $currentSessionId = session_id();
    $validLogSession = false;
    $logLookupFailed = false;

    if ($checkLog) {
        mysqli_stmt_bind_param($checkLog, 'ss', $loginSession, $currentSessionId);
        mysqli_stmt_execute($checkLog);
        mysqli_stmt_store_result($checkLog);
        $validLogSession = mysqli_stmt_num_rows($checkLog) === 1;
        mysqli_stmt_close($checkLog);
    } else {
        $logLookupFailed = true;
    }

    if (!$validLogSession) {
        if ($logLookupFailed) {
            $rejectSession('session_registry_error');
        }

        $findLogin = mysqli_prepare($logConnection, "SELECT 1 FROM `$logTable` WHERE login = ? LIMIT 1");
        $loginLogExists = false;
        if ($findLogin) {
            mysqli_stmt_bind_param($findLogin, 's', $loginSession);
            mysqli_stmt_execute($findLogin);
            mysqli_stmt_store_result($findLogin);
            $loginLogExists = mysqli_stmt_num_rows($findLogin) > 0;
            mysqli_stmt_close($findLogin);
        }

        $rejectSession($loginLogExists ? 'session_replaced' : 'log_row_missing');
    }
}

if (($checkConnected ?? 'true') != 'false' && !empty($_SESSION['loginSession'])) {
    $dateunix = date('U');
    $loginSession = $_SESSION['loginSession'];
    $tmpquery1 = 'UPDATE ' . $tableCollab['logs'] . " SET connected='$dateunix' WHERE login = '" . $loginSession . "'";
    connectSql($tmpquery1);
    $tmpsql = 'SELECT * FROM ' . $tableCollab['logs'] . " WHERE connected > $dateunix-5*60";
    compt($tmpsql);
    $connectedUsers = $countEnregTotal;
}

// disable actions if demo user logged in demo mode
if (!empty($action)) {
    if ($demoSession == 'true') {
        $closeTopic = '';
        $addToSiteTask = '';
        $removeToSiteTask = '';
        $addToSiteTopic = '';
        $removeToSiteTopic = '';
        $addToSiteTeam = '';
        $removeToSiteTeam = '';
        $action = '';
        $msg = 'demo';
    }
}

// set language session
if (!isset($_SESSION['languageSession'])) {
    $lang = $langDefault;
} else {
    $lang = $_SESSION['languageSession'];
}

// first load the English master language files
require_once($base_dir . 'languages/lang_en.php');
require_once($base_dir . 'languages/help_en.php');

if (file_exists($base_dir . 'languages/global_en.php' )) {
	include_once($base_dir . 'languages/global_en.php');
}

// then load language specific files if other than en (English)
if ($lang != 'en') {
    include_once($base_dir . 'languages/lang_' . $lang . '.php');
    include_once($base_dir . 'languages/help_' . $lang . '.php');
}

// then load the global language specific file, if present
if (file_exists($base_dir . 'languages/lang_' . $lang . '_global.php')) {
    include_once($base_dir . 'languages/lang_' . $lang . '_global.php');
}

// then load the auto-saved language specific file (eventually everything will be done with this
if (file_exists($base_dir . 'languages/global_' . $lang . '.php')) {
    include_once($base_dir . 'languages/global_' . $lang . '.php');
}

// then load the global non-language specific file, if present
if (file_exists($base_dir . 'languages/lang_global.php')) {
    include_once($base_dir . 'languages/lang_global.php');
}

// are notifications on?
if ($notifications == 'true') {
    // first load the phpmailer class
    include_once($base_dir . 'includes/phpmailer/class.phpmailer.php');
    // then load ours which extends the phpmailer class
    include_once($base_dir . 'includes/notification.class.php');
}

// time variables
if ($gmtTimezone == 'true') {
    $date = gmdate("Y-m-d");
    $dateheure = gmdate("Y-m-d H:i");
} else {
    $date = date("Y-m-d");
    $dateheure = date("Y-m-d H:i");
}

// update sorting table if query sort column
if (!empty($sor_cible) && $sor_champs != 'none') {
    $tmpquery = 'UPDATE ' . $tableCollab['sorting'] . " SET $sor_cible = '$sor_champs $sor_ordre' WHERE member = '" . $_SESSION['idSession'] . "'";
    connectSql($tmpquery);
}

// set all sorting values for logged user
if (isset($_SESSION['idSession'])) {
    $tmpquery = "WHERE sor.member = '" . $_SESSION['idSession'] . "'";
    $sortingUser = new request();
    $sortingUser->openSorting($tmpquery);
}

$setCopyright = '<!-- Powered by NetOffice v' . $version . ' -->';

// ---------------------------------------------------------------------------#
// FUNCTIONS
// ---------------------------------------------------------------------------#
/**
 * Connects to and selects the NetOffice database
 * 
 * @access public 
 */
function openDatabase() {
    global $MY_DBH,$databaseCharset;

    $host = MYSERVER;
    if (constant("DB_PCONNECT")) {
        if (strpos($host, 'p:') !== 0) {
            $host = 'p:' . $host;
        }
    }
    
    try {
        $MY_DBH = @mysqli_connect($host, MYLOGIN, MYPASSWORD, MYDATABASE);
    } catch (Throwable $exception) {
        $MY_DBH = null;
    }
    
    if (!$MY_DBH) {
        $error_msg = function_exists('mysqli_connect_error') ? mysqli_connect_error() : 'Connection failed';
        print '<li>Can not connect to ' . MYSERVER . ' as ' . MYLOGIN;
        print '<li>MySQL Error: ' . $error_msg;
        #header('Location: ../general/error.php?type=myserver');
        exit;
    }

	if ( $databaseCharset != '' ) {
        try {
            mysqli_query($MY_DBH, "SET NAMES '".$databaseCharset."'");
        } catch (Throwable $exception) {
            // Keep the connection usable with its server-default charset.
        }
    }
    return($MY_DBH);
}

/**
 * Checks for NetOffice releases greater than the current version installed
 * 
 * @param string $iCV Version to compare
 * @access public
 */
function updatechecker($iCV) {
    global $strings; 

    $checkMsg = '';

    // get latest available version number
    $versionData = @file('http://pxell.net/px/version.txt');
    if ($versionData === false) {
        return $checkMsg;
    }
    $iNV = trim(implode('', $versionData));

    // version comparisions
    // version string order: dev < alpha = a < beta = b < RC < pl
    // this allows for versions such as 2.5.2b1, 2.6.0RC1, and so on
    if (version_compare($iNV, $iCV, '>')) {
        $checkMsg  = '<br><b>' . $strings['update_available'] . '</b> ';
        $checkMsg .= $strings['version_current'] . " $iCV. ";
        $checkMsg .= $strings['version_latest'] . " $iNV.<br>";
        $checkMsg .= '<a href="http://www.sourceforge.net/projects/netoffice" target="_blank">';
        $checkMsg .= $strings['sourceforge_link'] . '</a>.';
    } 

    return($checkMsg);
}

/**
 * Calculate time to parse page (used with footer.php)
 * 
 * @access public 
 */
function getmicrotime() {
    list($usec, $sec) = explode(' ', microtime());
    return((float)$usec + (float)$sec);
}

/**
 * Automatic links
 * 
 * @param string $data Text to parse
 * @access public 
 */
function autoLinks($data) {
    global $newText;

    $newText = '';

    $lines = explode("\n", $data);

    foreach ($lines as $key => $line) {
        // make plain www. and ftp. into full URLs
        $line = preg_replace('/(^|[ \t])www\./i', ' http://www.', $line);
        $line = preg_replace('/(^|[ \t])ftp\./i', ' ftp://ftp.', $line);

        // link http, https and ftp URLs
        $line = preg_replace('/(http:\/\/[^ )\r\n]+)/i', '<a href="\\1" target="_blank">\\1</a>', $line);
        $line = preg_replace('/(https:\/\/[^ )\r\n]+)/i', '<a href="\\1" target="_blank">\\1</a>', $line);
        $line = preg_replace('/(ftp:\/\/[^ )\r\n]+)/i', '<a href="\\1" target="_blank">\\1</a>', $line);

        // link email addresses
        $line = preg_replace('/([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+))/i', '<a href="mailto:\\1">\\1</a>', $line);

        if (empty($newText)) {
            $newText = $line;
        } else {
            $newText .= "\n$line";
        }
    }
}

/**
 * Return number of day between 2 dates
 * 
 * @param string $date1 Date to compare
 * @param string $date2 Date to compare
 * @access public
 */
function diff_date($date1, $date2) {
    list($an, $mois, $jour) = explode('-', $date1, 3);
    list($an2, $mois2, $jour2) = explode('-', $date2, 3);
    $timestamp1 = mktime(0, 0, 0, $mois, $jour, $an);
    $timestamp2 = mktime(0, 0, 0, $mois2, $jour2, $an2);
    $diff = ($timestamp1 - $timestamp2) / (3600 * 24);
    $diff = intval($diff + 1);
    return($diff);
}

/**
 * Checks for password match using the globally specified login method
 * 
 * @param string $formUsername User name to test
 * @param string $formPassword User name password to test
 * @param string $storedPassword Password stored in database
 * @access public 
 */
function is_password_match($formUsername, $formPassword, $storedPassword) {
    global $loginMethod, $useLDAP, $configLDAP;

    // Existing users may have been created under a previous loginMethod.
    // Detect the stored representation instead of rejecting a correct
    // password solely because the setting later changed.
    $matchesStoredPassword = static function ($password, $stored) use ($loginMethod) {
        $stored = (string) $stored;
        if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
            return hash_equals(strtolower($stored), md5($password));
        }
        if ((strlen($stored) === 13 || substr($stored, 0, 1) === '$') &&
            $stored !== '*0' && $stored !== '*1') {
            return hash_equals($stored, crypt($password, $stored));
        }
        return $loginMethod === 'PLAIN' && hash_equals($stored, $password);
    };

    if ($useLDAP == 'true') {
        if ($formUsername == 'admin') {
            return $matchesStoredPassword($formPassword, $storedPassword);
        } 

        $conn = ldap_connect($configLDAP['ldapserver']);
        $sr = ldap_search($conn, $configLDAP['searchroot'], 'uid=' . $formUsername);
        $info = ldap_get_entries($conn, $sr);
        $user_dn = $info[0]['dn'];

        if (!$bind = ldap_bind($conn, $user_dn, $formPassword)) {
            return(false);
        } else {
            return(true);
        } 
    } else {
        return $matchesStoredPassword($formPassword, $storedPassword);
    }

    return false;
}

/**
 * Return a password using the globally specified method
 * 
 * @param string $newPassword Password to transfom
 * @access public 
 */
function get_password($newPassword) {
    global $loginMethod;

    switch ($loginMethod) {
        case 'MD5':
            return(md5($newPassword));
        case 'CRYPT':
            $salt = substr($newPassword, 0, 2);
            return(crypt($newPassword, $salt));
        case 'PLAIN':
            return($newPassword);

            return($newPassword);
    } 

    return $newPassword;
}

/**
 * Generate a random password
 * 
 * @param string $size Size of geenrated password
 * @param boolean $with_numbers Option to use numbers
 * @param boolean $with_tiny_letters Option to use tiny letters
 * @param boolean $with_capital_letters Option to use capital letters
 * @access public 
 */
function password_generator($size = 8, $with_numbers = true, $with_tiny_letters = true, $with_capital_letters = true) {
    global $pass_g;

    $pass_g = '';
    $sizeof_lchar = 0;
    $letter = '';
    $letter_tiny = 'abcdefghijklmnopqrstuvwxyz';
    $letter_capital = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $letter_number = '0123456789';

    if ($with_tiny_letters == true) {
        $sizeof_lchar += 26;

        if (isset($letter)) {
            $letter .= $letter_tiny;
        } else {
            $letter = $letter_tiny;
        } 
    } 

    if ($with_capital_letters == true) {
        $sizeof_lchar += 26;

        if (isset($letter)) {
            $letter .= $letter_capital;
        } else {
            $letter = $letter_capital;
        } 
    } 

    if ($with_numbers == true) {
        $sizeof_lchar += 10;

        if (isset($letter)) {
            $letter .= $letter_number;
        } else {
            $letter = $letter_number;
        } 
    } 

    if ($sizeof_lchar > 0) {
        srand((double)microtime() * date("YmdGis"));

        for ($cnt = 0; $cnt < $size; $cnt++) {
            $char_select = rand(0, $sizeof_lchar - 1);
            $pass_g .= $letter[$char_select];
        } 
    } 

    return($pass_g);
}

/**
 * Move a file in a new destination
 * 
 * @param string $source Current path of file
 * @param string $dest New path of file
 * @access public 
 */
function moveFile($source, $dest)
{
    global $mkdirMethod, $ftpRoot;

    if ($mkdirMethod == 'FTP') {
        $ftp = ftp_connect(FTPSERVER);
        ftp_login($ftp, FTPLOGIN, FTPPASSWORD);
        ftp_rename($ftp, $ftpRoot . '/' . $source, $ftpRoot . '/' . $dest);
        ftp_quit($ftp);
    } else {
        copy('../' . $source, '../' . $dest);
    } 
}

/**
 * Delete a file with a specified path
 * 
 * @param string $source Path of file
 * @access public 
 */
function deleteFile($source)
{
    global $mkdirMethod, $ftpRoot;

    if ($mkdirMethod == 'FTP') {
        $ftp = ftp_connect(FTPSERVER);
        ftp_login($ftp, FTPLOGIN, FTPPASSWORD);
        ftp_chdir($ftp, $ftpRoot);
        ftp_delete($ftp, $ftpRoot . '/' . $source);
        ftp_quit($ftp);
    } else {
        unlink('../' . $source);
    } 
}

/**
 * Upload a file to a specified destination
 * 
 * @param string $path Path of original file
 * @param string $source Temp file
 * @param string $dest Destination path
 * @access public 
 */
function uploadFile($path, $source, $dest)
{
    global $mkdirMethod, $ftpRoot;

    if ($mkdirMethod == 'FTP') {
        $pathNew = $ftpRoot . '/' . $path;
        $ftp = ftp_connect(FTPSERVER);
        ftp_login($ftp, FTPLOGIN, FTPPASSWORD);
        ftp_chdir($ftp, $pathNew);
        ftp_put($ftp, $dest, $source, FTP_BINARY);
        ftp_quit($ftp);
    } else if ($mkdirMethod == 'PHP') {
        #$source = str_replace('\\', '/', $source);;
        if (!is_dir('../' . $path)) {
            createDir($path);
        }
        $destination = '../' . $path . '/' . $dest;
        move_uploaded_file($source, $destination);
    } 
}

/**
 * Folder creation
 * 
 * @param string $path Path to the new directory
 * @access public 
 */
function createDir($path)
{
    global $mkdirMethod, $ftpRoot;

    $pathNew = explode('/', $path);
    $create_dir = '';

    if ($mkdirMethod == 'FTP') {
        $ftp = ftp_connect(FTPSERVER);
        ftp_login($ftp, FTPLOGIN, FTPPASSWORD);
        ftp_chdir($ftp, $ftpRoot);
        foreach ($pathNew as $dir) {
            $create_dir .= $dir . '/';
            @ftp_mkdir($ftp, $create_dir);
        } 
        ftp_quit($ftp);
    } else if ($mkdirMethod == 'PHP') {
        foreach ($pathNew as $dir) {
            $create_dir .= $dir . '/';
            @mkdir('../' . $create_dir, 0755);
        } 
    } 
}

/**
 * Folder recursive deletion
 * 
 * @param string $location Path of directory to delete
 * @access public 
 */
function delDir($location)
{
    if (is_dir($location)) {
        $all = opendir($location);

        while ($file = readdir($all)) {
            if (is_dir($location . '/' . $file) && $file != '..' && $file != '.') {
                deldir($location . '/' . $file);
                if (file_exists($location . '/' . $file)) {
                    rmdir($location . '/' . $file);
                } 
                unset($file);
            } else if (!is_dir($location . '/' . $file)) {
                if (file_exists($location . '/' . $file)) {
                    unlink("$location/$file");
                } 
                unset($file);
            } 
        } 

        closedir($all);
        rmdir($location);
    } else {
        if (file_exists($location)) {
            unlink($location);
        } 
    } 
}

/**
 * Return recursive folder size
 * 
 * @param string $location Path of directory to calculate
 * @param boolean $recursive Option to use recursivity
 * @access public 
 */
function folder_info_size($path, $recursive = true)
{
    $result = 0;

    if (is_dir($path) && is_readable($path)) {
        $dir = opendir($path);

        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . $file)) {
                    $result += $recursive ? folder_info_size($path . $file . '/') : 0;
                } else {
                    $result += filesize($path . $file);
                } 
            } 
        } 

        closedir($dir);
        return($result);
    }

    return 0;
}

/**
 * Return size converted with units (in the user language)
 * 
 * @param string $result Result to convert
 * @access public 
 */
function convertSize($result)
{
    global $byteUnits;

    if ($result >= 1073741824) {
        $result = round($result / 1073741824 * 100) / 100 . ' ' . $byteUnits[3];
    } else if ($result >= 1048576) {
        $result = round($result / 1048576 * 100) / 100 . ' ' . $byteUnits[2];
    } else if ($result >= 1024) {
        $result = round($result / 1024 * 100) / 100 . ' ' . $byteUnits[1];
    } else {
        $result = $result . ' ' . $byteUnits[0];
    } 

    if ($result == 0) {
        $result = '-';
    } 

    return($result);
}

/**
 * Return file size
 * 
 * @param string $fichier File used
 * @access public 
 */
function file_info_size($fichier)
{
    global $taille;
    $taille = filesize($fichier);
    return($taille);
}

/**
 * Return file dimensions
 * 
 * @param string $fichier File used
 * @access public 
 */
function file_info_dim($fichier)
{
    global $dim;
    $temp = GetImageSize($fichier);
    $dim = is_array($temp) ? $temp[0] . 'x' . $temp[1] : '';
    return($dim);
}

/**
 * Return file date
 * 
 * @param string $fichier File used
 * @access public 
 */
function file_info_date($fichier)
{
    global $dateFile;
    $dateFile = date("Y-m-d", filemtime($fichier));
    return($dateFile);
}

/**
 * Read the content of a file
 * 
 * @param string $file File used
 * @access public 
 */
function recupFile($file)
{
    $content = '';

    if (!file_exists($file)) {
        echo 'File does not exist : ' . $file;
        return(false);
    } 

    $fp = fopen($file, 'r');

    if (!$fp) {
        echo 'Unable to open file : ' . $file;
        return(false);
    } while (!feof ($fp)) {
        $tmpline = fgets($fp, 4096);
        $content .= $tmpline;
    } 

    fclose($fp);
    return($content);
}

/**
 * Displat date according to timezone (if timezone enabled)
 * 
 * @param string $storedDate Date stored in database
 * @param string $gmtUser User timezone
 * @access public 
 */
function createDate($storedDate, $gmtUser)
{
    global $gmtTimezone;

    if ($gmtTimezone == 'true') {
        if ($storedDate != '') {
            $extractHour = substr($storedDate, 11, 2);
            $extractMinute = substr($storedDate, 14, 2);
            $extractYear = substr($storedDate, 0, 4);
            $extractMonth = substr($storedDate, 5, 2);
            $extractDay = substr($storedDate, 8, 2);

            return(date("Y-m-d H:i", mktime($extractHour + $gmtUser, $extractMinute, '', $extractMonth, $extractDay, $extractYear)));
        } 
    } else {
        return($storedDate);
    }

    return $storedDate;
}

/**
 * Convert insert data value in form
 * 
 * @param string $data Data to convert
 * @access public 
 */
function convertData($data)
{
    $data = str_replace('"', '&quot;', $data);
    $data = str_replace('<', '&lt;', $data);
    $data = str_replace('>', '&gt;', $data);
    return addslashes($data);
}

/**
 * Count total results from a request
 * 
 * @param string $tmpsql Sql query
 * @access public 
 */
function compt($tmpsql)
{
    global $tableCollab, $databaseType, $countEnregTotal, $comptRequest;

    $countEnreg = array();
    $countEnregTotal = 0;
    $comptRequest = (int) ($comptRequest ?? 0) + 1;

    if ($databaseType == 'mysql') {
        $res = openDatabase();
        $sql = $tmpsql;
        $index = mysqli_query($res, $sql);

        if ($index instanceof mysqli_result) {
            while ($row = mysqli_fetch_row($index)) {
                $countEnreg[] = ($row[0]);
            }

            $countEnregTotal = count($countEnreg);
            mysqli_free_result($index);
        }
        @mysqli_close($res);
    } 

    return($countEnregTotal);
}

/**
 * Simple query
 * 
 * @param string $tmpsql Sql query
 * @access public 
 */
function connectSql($tmpsql)
{
    global $tableCollab, $databaseType, $lastInsertId, $lastSqlError;

    $lastInsertId = null;
    $lastSqlError = '';

    if ($databaseType == 'mysql') {
        $res = openDatabase();
        $sql = $tmpsql;
        try {
            $index = mysqli_query($res, $sql);
        } catch (Throwable $exception) {
            $lastSqlError = $exception->getMessage();
            @mysqli_close($res);
            throw new RuntimeException('Database query failed: ' . $lastSqlError, 0, $exception);
        }
        if ($index === false) {
            $lastSqlError = mysqli_error($res);
            @mysqli_close($res);
            throw new RuntimeException('Database query failed: ' . $lastSqlError);
        }
        $insertId = mysqli_insert_id($res);
        if ($insertId > 0) {
            $lastInsertId = $insertId;
        }
        if ($index instanceof mysqli_result) {
            mysqli_free_result($index);
        }
        @mysqli_close($res);
        return true;
    } 

    return false;
}

/**
 * Return last id from any table
 * 
 * @param string $tmpsql Table name
 * @access public
 */
function last_id($tmpsql)
{
    global $tableCollab, $databaseType, $lastId, $lastInsertId, $lastSqlError;

    // Keep the historical global for legacy callers while also returning the
    // value for modern, explicit callers.
    $lastId = array();

    // connectSql() captured this from the same connection that performed the
    // INSERT. This is concurrency-safe and cannot select another user's row.
    if ($lastInsertId !== null) {
        $lastId[] = $lastInsertId;
        $lastInsertId = null;
        return $lastId;
    }

    if ($lastSqlError !== '') {
        throw new RuntimeException('Database INSERT failed: ' . $lastSqlError);
    }

    if ($databaseType == 'mysql') {
        $res = openDatabase();
        $sql = 'SELECT id FROM ' . $tmpsql . ' ORDER BY id DESC';
        $index = mysqli_query($res, $sql);

        if ($index instanceof mysqli_result) {
            while ($row = mysqli_fetch_row($index)) {
                $lastId[] = $row[0];
            }

            mysqli_free_result($index);
        }
        @mysqli_close($res);
    }

    return $lastId;
}

/**
 * Convert an array to a CSV line
 * 
 * @author Michael Cook <mcook83@sourceforge.net> 
 * @param array $row Row of data
 * @access public 
 */
function write_csv($row)
{
    $line = '';

    foreach ($row as $value) {
        if (is_numeric($value)) {
            $line .= $value . ',';
        } else {
            $line .= '"' . $value . '",';
        } 
    } 

    $line = substr($line, 0, -1);
    $line .= "\n";
    return($line);
}

// This function is called by the session handler to initialize things
// this NEEDS persistent database connections!!
function _sess_mysql_connect()
{
    global $MY_DBH, $databaseCharset;

    if ($MY_DBH instanceof mysqli) {
        try {
            $connectionCheck = @mysqli_query($MY_DBH, 'SELECT 1');
            if ($connectionCheck instanceof mysqli_result) {
                mysqli_free_result($connectionCheck);
                return true;
            }
        } catch (Throwable $exception) {
            // Other query helpers may have closed the shared connection.
        }

        $MY_DBH = null;
    }

    $host = MYSERVER;
    if (DB_PCONNECT && strpos($host, 'p:') !== 0) {
        $host = 'p:' . $host;
    }

    // A session callback must report failure instead of exiting. Exiting while
    // PHP is saving a session at shutdown recursively invokes the handler.
    try {
        $MY_DBH = @mysqli_connect($host, MYLOGIN, MYPASSWORD, MYDATABASE);
    } catch (Throwable $exception) {
        $MY_DBH = null;
        return false;
    }
    if (!$MY_DBH) {
        return false;
    }

    if ($databaseCharset != '' && !_sess_mysql_query("SET NAMES '" . $databaseCharset . "'")) {
        return false;
    }

    return true;
}

function _sess_mysql_query($query)
{
    global $MY_DBH;

    if (!($MY_DBH instanceof mysqli)) {
        return false;
    }

    try {
        return @mysqli_query($MY_DBH, $query);
    } catch (Throwable $exception) {
        return false;
    }
}

function _sess_mysql_open($save_path, $session_name)
{
    return _sess_mysql_connect();
}

// This function is called when the page has finished executing and the session
// handler needs to close things off.
//
// (Note, do not confuse this with _sess_mysql_destroy(), which is called to
// kill the session).
function _sess_mysql_close()
{ 
    global $MY_DBH; 

    // Closes non-persistent database connections
    if (!($MY_DBH instanceof mysqli)) {
        return true;
    }

    try {
        if (@mysqli_close($MY_DBH) != true) {
            return(false);
        }
    } catch (Throwable $exception) {
        // The application's query helpers may already have closed this handle.
    }

    $MY_DBH = null;

    return(true);
}

// This function is called by the session handler to read the data associated
// with a given session key ($key) and remote address (SESS_REMOTE_ADDR). This
// function must retrieve and return the session data for the identified session.
//
// (Note: you do not have to worry about unserializing data, if you do not know
// what this means, then don't worry about it).
function _sess_mysql_read($session_id)
{
    global $MY_DBH, $tableCollab;

    $data = ''; // init
    $valid_session_time = time() - SESS_MAXLIFE; // earliest valid session time
     
    // Select statement
    $select = 'SELECT session_data ';
    $select .= 'FROM ' . $tableCollab['sessions'] . ' ';
    $select .= 'WHERE id="' . $session_id . '" '; 

    // shall we check the ip?
    if (constant("SESS_IPCHECK") == true) {
        $select .= 'AND ipaddr="' . SESS_REMOTE_ADDR . '" ';
    } 

    $select .= 'AND last_access > ' . $valid_session_time; 

    // Check the database connection without exiting from the session callback.
    if (!_sess_mysql_connect()) {
        return '';
    }

    // Execute the query
    if (!$result = _sess_mysql_query($select)) {
        // error with query
        return '';
    } 

    // Check for result, must only be one to return data
    if (mysqli_num_rows($result) == 1) {
        // Session data found, strip any slashes used for escaping
        $row = mysqli_fetch_array($result);
        $data = stripSlashes($row['session_data']);
    } else {
        // We have an invalid or stale session, destroy it!
        _sess_mysql_destroy($session_id);
    }

    // Free up the resources used by the statement
    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }

    return($data);
}

// This function is called when the session handler has session data to save,
// which usually happens at the end of your script. It is responsible for saving
// the session data in such a way that it can be retrieved later on by
// _sess_mysql_read($session_id).
//
// (Note: you do not have to worry about serializing data, if you do not know
// what this means, then don't worry about it).
function _sess_mysql_write($session_id, $val)
{
    global $MY_DBH, $tableCollab; 

    // Escape any characters needed for storage within a database
    $data = addSlashes($val);
    $valid_session_time = time() - SESS_MAXLIFE; // earliest valid session time
     
    // Insert statement for initial session entry
    $insert = 'INSERT INTO ' . $tableCollab['sessions'] . ' (id, ipaddr, session_data, last_access) ';
    $insert .= 'VALUES ("' . $session_id . '", "' . SESS_REMOTE_ADDR . '", "' . $data . '", "' . time() . '")'; 

    // Update statements for current/active sessions
    $update = 'UPDATE ' . $tableCollab['sessions'] . ' SET session_data="' . $data . '", last_access=' . time() . ' ';
    $update .= 'WHERE id="' . $session_id . '" ';

    // shall we check the ip?
    if (constant("SESS_IPCHECK") == true) {
        $update .= 'AND ipaddr="' . SESS_REMOTE_ADDR . '" ';
    } 

    $update .= 'AND last_access > ' . $valid_session_time; 

    // Check the database connection without exiting during request shutdown.
    if (!_sess_mysql_connect()) {
        return false;
    }

    // First try the insert, if that doesn't succeed, it means the
    // session already exists and we need to update it instead.
    if (!_sess_mysql_query($insert)) {
        // Insert failed, issue an update
        if (!_sess_mysql_query($update)) {
            // Everything faild, return false
            return(false);
        } 
    } 

    return(true);
}

// This function is called when a session is destroyed. It is responsible for
// deleting the session and cleaning things up.
function _sess_mysql_destroy($session_id)
{
    global $MY_DBH, $tableCollab; 

    // Delete from the sessions table all data for the session $session_id
    // and SESS_REMOTE_ADDR
    $select = 'DELETE FROM ' . $tableCollab['sessions'] . ' WHERE id="' . $session_id . '"'; 

    // shall we check the ip?
    if (constant("SESS_IPCHECK") == true) {
        $select .= ' AND ipaddr="' . SESS_REMOTE_ADDR . '"';
    } 

    if (!_sess_mysql_connect()) {
        return false;
    }

    // Execute the query
    if (!$result = _sess_mysql_query($select)) {
        // error with query
        return false;
    } 

    return($result);
}

// This function is responsible for garbage collection. In the case of session
// handling, it is responsible for deleting old, stale sessions that are hanging
// around. The session handler will call this every now and then.
function _sess_mysql_gc($max_lifetime)
{
    global $MY_DBH, $tableCollab;

    $valid_session_time = time() - 172800; // 48 hrs ago
     
    // Delete old values from the sessions table, greater than 48 hrs old
    $query = 'DELETE FROM ' . $tableCollab['sessions'] . ' WHERE last_access < ' . $valid_session_time; 

    if (!_sess_mysql_connect()) {
        return false;
    }

    // Execute the query
    if (!$result = _sess_mysql_query($query)) {
        // error with query
        return false;
    } 

    return($result);
}

// this function returns the client host address
function get_http_host()
{ 
    // Get the refering host for security checks
    $sess_host = $_SERVER['HTTP_HOST'] ?? '';

    if (empty($sess_host)) {
        $sess_host = getenv('HTTP_HOST');
    } 

    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $sess_host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } 

    $sess_host = preg_replace('/:.*/', '', $sess_host);
    $sess_host = trim($sess_host); // strip leading/trailing white space
    
    return($sess_host);
}

// Get the (actual) client IP addr, used in combination with
// the SID as the primary key in the database, security.
function get_remote_addr()
{
    $ipaddr = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($ipaddr)) {
        $ipaddr = getenv('REMOTE_ADDR');
    }

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddr = $_SERVER['HTTP_CLIENT_IP'];
    } 

    $tmpipaddr = getenv('HTTP_CLIENT_IP');

    if (!empty($tmpipaddr)) {
        $ipaddr = $tmpipaddr;
    } 

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddr = preg_replace('/,.*/', '', $_SERVER['HTTP_X_FORWARDED_FOR']);
    } 

    $tmpipaddr = getenv('HTTP_X_FORWARDED_FOR');

    if (!empty($tmpipaddr)) {
        $ipaddr = preg_replace('/,.*/', '', $tmpipaddr);
    } 

    if (isset($_SERVER['HTTP_CLIENTADDRESS'])) {
        $tmpipaddr = $_SERVER['HTTP_CLIENTADDRESS'];

        if (!empty($tmpipaddr)) {
            $ipaddr = preg_replace('/,.*/', '', $tmpipaddr);
        } 
    } 

    $ipaddr = trim($ipaddr);

    return($ipaddr);
}

/**
 * Return timestamp of date
 *
 * @param string $date1 Date to transform
 */
function date2timestamp($date1) {
    list($an1, $mois1, $jour1) = explode('-', $date1, 3);
    $timestamp1 = mktime(0, 0, 0, $mois1, $jour1, $an1);
    return($timestamp1);
}

/**
 * Return number of hour between 2 dates
 *
 * @param string $date1 Date to compare
 * @param string $date2 Date to compare
 * @access public
 */
function diff_hour($date1, $date2) {
    global $dayHourArray;

    $timestamp1 = date2timestamp($date1);
    $timestamp2 = date2timestamp($date2);
    $diff = 0;
    while ($timestamp1<=$timestamp2) {
        $currDate = date("Y-m-d", $timestamp1);
        $tmpquery = " WHERE hol.date='$currDate'";
        $listHoliday = new request();
        $listHoliday->openHoliday($tmpquery);
        $comptListHoliday = count($listHoliday->hol_id ?? array());
        if ($comptListHoliday == 0) {
            $weekDay = date("w", $timestamp1);
            $diff += (float) ($dayHourArray[$weekDay] ?? 0);
        }
        $timestamp1 += (3600 * 24);
    }
    return($diff);
}

/**
 * Return date some hours after
 *
 * @param string $date1 Date to compare
 * @param string $hour1 Hours after
 * @access public
 */
function hours_after($date1, $hour1) {
    global $dayHourArray;

    $timestamp1 = date2timestamp($date1);
    $diff = 0;
    while ($diff < $hour1) {
        $currDate = date("Y-m-d", $timestamp1);
        $tmpquery = " WHERE hol.date='$currDate'";
        $listHoliday = new request();
        $listHoliday->openHoliday($tmpquery);
        $comptListHoliday = count($listHoliday->hol_id ?? array());
        if ($comptListHoliday == 0) {
            $weekDay = date("w", $timestamp1);
            $diff += (float) ($dayHourArray[$weekDay] ?? 0);
            if ($diff >= $hour1)
                break;
        }
        $timestamp1 += (3600 * 24);
    }
    return(date("Y-m-d", $timestamp1));
}

/**
 * Return date some hours before
 *
 * @param string $date1 Date to compare
 * @param string $hour1 Hours before
 * @access public
 */
function hours_before($date1, $hour1) {
    global $dayHourArray;

    $timestamp1 = date2timestamp($date1);
    $diff = 0;
    while ($diff < $hour1) {
        $currDate = date("Y-m-d", $timestamp1);
        $tmpquery = " WHERE hol.date='$currDate'";
        $listHoliday = new request();
        $listHoliday->openHoliday($tmpquery);
        $comptListHoliday = count($listHoliday->hol_id ?? array());
        if ($comptListHoliday == 0) {
            $weekDay = date("w", $timestamp1);
            $diff += (float) ($dayHourArray[$weekDay] ?? 0);
            if ($diff >= $hour1)
                break;
        }
        $timestamp1 -= (3600 * 24);
    }
    return(date("Y-m-d", $timestamp1));
}

// dylan - new function that everyone should start using instead of accessing
// global strings table for translations

function text( $string )
{
	global $strings;

	if (isset($strings[$string])) {
	    return $strings[ $string ];
	} else {
	   return $string;
	}
}

?>
