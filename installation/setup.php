<?php // $Revision: 1.16 $
/* vim: set expandtab ts=4 sw=4 sts=4: */
/**
 * $Id: setup.php,v 1.16 2005/06/11 19:30:41 madbear Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

error_reporting(E_ALL & ~E_NOTICE);

require_once('../includes/error_handler.php');
require_once('../languages/help_en.php');

define("INSTALL", true);

$step = $_REQUEST['step'] ?? '';
$connexion = $_REQUEST['connexion'] ?? '';
$redirect = $_REQUEST['redirect'] ?? '';
$action = $_REQUEST['action'] ?? '';
$updatechecker = $_REQUEST['updatechecker'] ?? '';
$installationType = $_REQUEST['installationType'] ?? '';
$databaseType = $_REQUEST['databaseType'] ?? '';
$myserver = $_REQUEST['myserver'] ?? '';
$mylogin = $_REQUEST['mylogin'] ?? '';
$mypassword = $_REQUEST['mypassword'] ?? '';
$mydatabase = $_REQUEST['mydatabase'] ?? '';
$myprefix = $_REQUEST['myprefix'] ?? '';
$mkdirMethod = $_REQUEST['mkdirMethod'] ?? '';
$notifications = $_REQUEST['notifications'] ?? '';
$forcedlogin = $_REQUEST['forcedlogin'] ?? '';
$langdefault = $_REQUEST['langdefault'] ?? '';
$root = $_REQUEST['root'] ?? '';
$loginMethod = $_REQUEST['loginMethod'] ?? '';
$adminPwd = $_REQUEST['adminPwd'] ?? '';
$ftpserver = $_REQUEST['ftpserver'] ?? '';
$ftplogin = $_REQUEST['ftplogin'] ?? '';
$ftppassword = $_REQUEST['ftppassword'] ?? '';
$ftpRoot = $_REQUEST['ftpRoot'] ?? '';
$error = '';
$cryptKey = get_crypt_key();
$basedir = preg_replace('/installation$/i', '', str_replace('\\', '/', dirname(__FILE__)), 1);

if ($redirect == "true" && $step == "2") {
    header("Location: ../installation/setup.php?step=2&connexion=$connexion");
}

if (substr($root, -1) == "/") {
    $root = substr($root, 0, -1);
}

if (substr($ftpRoot, -1) == '/') {
    $ftpRoot = substr($ftpRoot, 0, -1);
}

$version = '2.7.2B';

$dateheure = date("Y-m-d H:i");

if ($action == "generate") {
    if ($myserver == '') {
        $error = 'Must be insert the database Server';
    } else if ($mylogin == '') {
        $error = 'Must be insert the database Login';
    } else if ($mydatabase == '') {
        $error = 'Must be insert the database Name';
    } else if ($root == '') {
        $error = 'Must be insert the Root path';
    } else if ($adminPwd == '') {
        $error = 'Must be insert the Admin password';
    }

    if ($installationType == "offline") {
        $updatechecker = "false";
    }

    require_once('./setup_settings.php');

    if (!$error) {
        $fp = fopen('../includes/settings.php', 'wb+');
        $fw = fwrite($fp, $content);
        
        if (!$fw) {
            $error = 1;
            echo "<br><b>PANIC! <br> settings.php can't be written!</b><br>";
        }
        
        fclose($fp);
        $msg = 'File settings.php created correctly.';
        // crypt admin and demo password
        $demoPwd = get_password("demo");
        $adminPwd = get_password($adminPwd);
        // create all tables
        require_once("./db_var.inc.php");
        require_once("./setup_db.php");
        if ($databaseType == "mysql") {
            $my = mysqli_connect($myserver, $mylogin, $mypassword, $mydatabase);
            if (!$my || mysqli_connect_error()) {
                print '<br><b>PANIC! <br> Error during connection on server MySQL.</b><br>';
                print "[". mysqli_connect_errno() . "] " . mysqli_connect_error() ."<br/>\n";
                exit;
            }

            if (mysqli_errno($my) != 0) {
                exit('<br><b>PANIC! <br> Error during selection database.</b><br>');
            }

            for($con = 0; $con < count($SQL); $con++) {
                mysqli_query($my, $SQL[$con]);
                // echo $SQL[$con] . ';<br>';
                
                if (mysqli_errno($my) != 0) {
                    exit('<br><b>PANIC! <br> Error during the creation of the tables.</b><br> Error: ' . mysqli_error($my));
                }
            }
        }

        $msg .= '<br>Tables and settings file created correctly.';
        $msg .= '<br><br><a href=../general/login.php>Please log in</a>';
    } else {
        $msg = $error;
    } 
} 

if ($step == "") {
    $step = "1";
} 

$setTitle = "Online Project Management";
define('THEME', 'deepblue');
$blank = "true";
require_once("../themes/" . THEME . "/block.class.php");


$breadcrumbs[]="<a href=\"../installation/setup.php\">Setup</a>";

if ($step == "1") {
    $breadcrumbs[]="License";
} else if ($step > "1") {
    $breadcrumbs[]="<a href=\"../installation/setup.php?step=1\">License</a>";
    if ($step == "2") {
        $breadcrumbs[]="Settings";
    } else if ($step > "2") {
        $breadcrumbs[]="<a href=\"../installation/setup.php?step=2\">Settings</a>";
        if ($step == "3") {
            $breadcrumbs[]="Control";
        } 
    } 
} 

//--- hack by pixtur -------
// NOTE:
// - those globals are required by 'header.php' and normally defined at library.php 
// - but library can NOT be included here, because it requires database to be setup.
// -
{
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

	$notLogged=true;

	require_once("../themes/" . THEME . "/header.php");
}

$block1 = new block();

if ($step == "1") {
    $block1->headingForm("License");
}
else if ($step == "2") {
    $block1->headingForm("Settings");
}
else if ($step == "3") {
    $block1->headingForm("Control");
}

if ($step == "1") {
    $block1->openContent();
    //$block1->contentTitle("&nbsp;");

    echo '<div class="mb-3"><div class="license p-3 border rounded bg-light">';
    include_once('../docs/copying.txt');
    echo "</div></div>";
    $block1->closeContent();
}

if ($step == "2") {
    $block1->openContent();
    $block1->contentTitle("Details");
    $block1->form = "settings";
    $block1->openForm("../installation/setup.php?action=generate&amp;step=3");

    $installCheckOffline = '';
    $installCheckOnline = '';
    $dbCheckMysql = '';
    $checked1_a = '';
    $checked2_a = '';
    $checked1_b = '';
    $checked2_b = '';

    if ($connexion == "off") {
        echo "<input value=\"false\" name=\"updatechecker\" type=\"hidden\">";
    } else if (is_readable(dirname(__DIR__) . '/version.txt')) {
        echo "<input value=\"true\" name=\"updatechecker\" type=\"hidden\">";
    } else {
        echo "<input value=\"false\" name=\"updatechecker\" type=\"hidden\">";
    } 

    if ($connexion == "off") {
        $installCheckOffline = "checked";
    } else {
        $installCheckOnline = "checked";
    } 

    if ($databaseType == "mysql" || $databaseType == "") {
        $dbCheckMysql = "checked";
    } 

    echo '<div class="mb-3">
        <label class="form-label">* Installation type:</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="installationType" id="installTypeOffline" value="offline" ' . $installCheckOffline . '>
            <label class="form-check-label" for="installTypeOffline">Offline (firewall/intranet, no update checker)</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="installationType" id="installTypeOnline" value="online" ' . $installCheckOnline . '>
            <label class="form-check-label" for="installTypeOnline">Online</label>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">* Database type:</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="databaseType" id="dbTypeMysql" value="mysql" ' . $dbCheckMysql . '>
            <label class="form-check-label" for="dbTypeMysql">MySQL</label>
        </div>
    </div>
    <div class="mb-3">
        <label for="myserver" class="form-label">* Database server:</label>
        <input type="text" class="form-control" id="myserver" name="myserver" value="' . htmlspecialchars($myserver) . '" maxlength="100" required>
    </div>
    <div class="mb-3">
        <label for="mylogin" class="form-label">* Database login:</label>
        <input type="text" class="form-control" id="mylogin" name="mylogin" value="' . htmlspecialchars($mylogin) . '" maxlength="100" required>
    </div>
    <div class="mb-3">
        <label for="mypassword" class="form-label">Database password:</label>
        <input type="password" class="form-control" id="mypassword" name="mypassword" value="' . htmlspecialchars($mypassword) . '" maxlength="100">
    </div>
    <div class="mb-3">
        <label for="mydatabase" class="form-label">* Database name:</label>
        <input type="text" class="form-control" id="mydatabase" name="mydatabase" value="' . htmlspecialchars($mydatabase) . '" maxlength="100" required>
    </div>
    <div class="mb-3">
        <label for="myprefix" class="form-label">Table prefix: [<a href="javascript:void(0);" onmouseover="return overlib(\'' . addslashes($help["setup_myprefix"]) . '\',ABOVE,SNAPX,550,BGCOLOR,\'#5B7F93\',FGCOLOR,\'#C4D3DB\');" onmouseout="return nd();">Help</a>]</label>
        <input type="text" class="form-control" id="myprefix" name="myprefix" value="' . htmlspecialchars($myprefix) . '" maxlength="100">
    </div>';

    $safemodeTest = ini_get('safe_mode');
    if ($safemodeTest == "1") {
        $checked1_a = "checked"; //false
        $safemode = "on";
    } else {
        $checked2_a = "checked"; //true
        $safemode = "off";
    } 

    $notificationsTest = function_exists('mail');
    if ($notificationsTest == "true") {
        $checked2_b = "checked"; //false
        $gdlibrary = "on";
    } else {
        $checked1_b = "checked"; //true
        $gdlibrary = "off";
    } 

    echo '<div class="mb-3">
        <label class="form-label">* Create folder method: [<a href="javascript:void(0);" onmouseover="return overlib(\'' . addslashes($help["setup_mkdirMethod"]) . '\',SNAPX,550,BGCOLOR,\'#5B7F93\',FGCOLOR,\'#C4D3DB\');" onmouseout="return nd();">Help</a>]</label>
        <div class="row">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="mkdirMethod" id="mkdirFTP" value="FTP" ' . $checked1_a . '>
                    <label class="form-check-label" for="mkdirFTP">FTP</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="mkdirMethod" id="mkdirPHP" value="PHP" ' . $checked2_a . '>
                    <label class="form-check-label" for="mkdirPHP">PHP</label>
                </div>
                <small class="text-muted">[Safe-mode ' . $safemode . ']</small>
            </div>';
    if ($safemodeTest == "1") {
        echo '<div class="col-md-6">
                <div class="mb-2">
                    <label for="ftpserver" class="form-label">FTP server:</label>
                    <input type="text" class="form-control" id="ftpserver" name="ftpserver" value="' . htmlspecialchars($ftpserver) . '" maxlength="100">
                </div>
                <div class="mb-2">
                    <label for="ftplogin" class="form-label">FTP login:</label>
                    <input type="text" class="form-control" id="ftplogin" name="ftplogin" value="' . htmlspecialchars($ftplogin) . '" maxlength="100">
                </div>
                <div class="mb-2">
                    <label for="ftppassword" class="form-label">FTP password:</label>
                    <input type="password" class="form-control" id="ftppassword" name="ftppassword" value="' . htmlspecialchars($ftppassword) . '" maxlength="100">
                </div>
                <div class="mb-2">
                    <label for="ftpRoot" class="form-label">FTP root:</label>
                    <input type="text" class="form-control" id="ftpRoot" name="ftpRoot" value="' . htmlspecialchars($ftpRoot) . '" maxlength="100">
                </div>
            </div>';
    } 
    echo '</div></div>
    <div class="mb-3">
        <label class="form-label">* Notifications: [<a href="javascript:void(0);" onmouseover="return overlib(\'' . addslashes($help["setup_notifications"]) . '\',SNAPX,550,BGCOLOR,\'#5B7F93\',FGCOLOR,\'#C4D3DB\');" onmouseout="return nd();">Help</a>]</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="notifications" id="notifFalse" value="false" ' . $checked1_b . '>
            <label class="form-check-label" for="notifFalse">False</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="notifications" id="notifTrue" value="true" ' . $checked2_b . '>
            <label class="form-check-label" for="notifTrue">True</label>
        </div>
        <small class="text-muted">[Mail ' . $gdlibrary . ']</small>
    </div>
    <div class="mb-3">
        <label class="form-label">* Forced login: [<a href="javascript:void(0);" onmouseover="return overlib(\'' . addslashes($help["setup_forcedlogin"]) . '\',SNAPX,550,BGCOLOR,\'#5B7F93\',FGCOLOR,\'#C4D3DB\');" onmouseout="return nd();">Help</a>]</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="forcedlogin" id="forcedLoginFalse" value="false" checked>
            <label class="form-check-label" for="forcedLoginFalse">False</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="forcedlogin" id="forcedLoginTrue" value="true">
            <label class="form-check-label" for="forcedLoginTrue">True</label>
        </div>
    </div>
    <div class="mb-3">
        <label for="langdefault" class="form-label">Default language: [<a href="javascript:void(0);" onmouseover="return overlib(\'' . addslashes($help["setup_langdefault"]) . '\',SNAPX,550,BGCOLOR,\'#5B7F93\',FGCOLOR,\'#C4D3DB\');" onmouseout="return nd();">Help</a>]</label>
        <select class="form-select" name="langdefault" id="langdefault">
            <option value="">Blank</option>
            <option value="az">Azerbaijani</option>
            <option value="pt-br">Brazilian Portuguese</option>
            <option value="bg">Bulgarian</option>
            <option value="ca">Catalan</option>
            <option value="zh">Chinese simplified</option>
            <option value="zh-tw">Chinese traditional</option>
            <option value="cs-iso">Czech (iso)</option>
            <option value="cs-win1250">Czech (win1250)</option>
            <option value="da">Danish</option>
            <option value="nl">Dutch</option>
            <option value="en">English</option>
            <option value="et">Estonian</option>
            <option value="fr">French</option>
            <option value="de">German</option>
            <option value="hu">Hungarian</option>
            <option value="is">Icelandic</option>
            <option value="in">Indonesian</option>
            <option value="it">Italian</option>
            <option value="ko">Korean</option>
            <option value="lv">Latvian</option>
            <option value="no">Norwegian</option>
            <option value="pl">Polish</option>
            <option value="pt">Portuguese</option>
            <option value="ro">Romanian</option>
            <option value="ru">Russian</option>
            <option value="sk-win1250">Slovak (win1250)</option>
            <option value="es">Spanish</option>
            <option value="sv">Swedish</option>
            <option value="tr">Turkish</option>
            <option value="uk">Ukrainian</option>
        </select>
    </div>';

    $serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $serverPort = (int) ($_SERVER['SERVER_PORT'] ?? 80);
    $httpsEnabled = strtolower((string) ($_SERVER['HTTPS'] ?? 'off')) === 'on';
    $scriptName = $_SERVER['PHP_SELF'] ?? '/installation/setup.php';

    $url = $serverName;
    if ($serverPort !== 80 && $serverPort !== 443) {
        $url .= ":" . $serverPort;
    } 
    if ($httpsEnabled) {
        $protocol = "https://";
    } else {
        $protocol = "http://";
    } 
    $root = $protocol . $url . dirname($scriptName);
    $root = str_replace("installation", "", $root);

    echo '<div class="mb-3">
        <label for="root" class="form-label">* Root:</label>
        <input type="text" class="form-control" id="root" name="root" value="' . htmlspecialchars($root) . '" maxlength="100" required>
    </div>
    <div class="mb-3">
        <label class="form-label">* Login method: [<a href="javascript:void(0);" onmouseover="return overlib(\'' . addslashes($help["setup_loginmethod"]) . '\',SNAPX,550,BGCOLOR,\'#5B7F93\',FGCOLOR,\'#C4D3DB\');" onmouseout="return nd();">Help</a>]</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="loginMethod" id="loginPlain" value="PLAIN">
            <label class="form-check-label" for="loginPlain">Plain</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="loginMethod" id="loginMD5" value="MD5">
            <label class="form-check-label" for="loginMD5">MD5</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="loginMethod" id="loginCrypt" value="CRYPT" checked>
            <label class="form-check-label" for="loginCrypt">Crypt</label>
        </div>
    </div>
    <div class="mb-3">
        <label for="adminPwd" class="form-label">* Admin password:</label>
        <input type="password" class="form-control" id="adminPwd" name="adminPwd" value="' . htmlspecialchars($adminPwd) . '" maxlength="100" required>
    </div>
    <div class="mb-3">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>';
    $block1->closeContent();
    $block1->closeForm();
} 

if ($step == "3") {
    $block1->openContent();
    $block1->contentTitle("&nbsp;");

    echo '<div class="alert alert-info">' . $msg . '</div>';
    $block1->closeContent();
} 
$block1->headingForm_close();

$stepNext = $step + 1;
if ($step < "2") {
    echo '<form name="license" action="../installation/setup.php?step=2&amp;redirect=true" method="post" class="text-center">
        <a href="javascript:document.license.submit();" class="btn btn-primary btn-lg"><b>Step ' . $stepNext . '</b></a>
        <br><br>
        <div class="form-check d-inline-block">
            <input class="form-check-input" type="checkbox" value="off" name="connexion" id="connexionOffline">
            <label class="form-check-label" for="connexionOffline">Offline installation (firewall/intranet, no update checker)</label>
        </div>
    </form><br>';
} 

$footerDev = false;
require_once("../themes/" . THEME . "/footer.php");

// Generates the unique [en|de]cryption key for your installation
function get_crypt_key()
{
  srand((double)microtime()*1000000);
  return(md5(uniqid(rand(),1)));
}

// return a password using the globally specified method
function get_password($newPassword)
{
    global $loginMethod;

    switch ($loginMethod) {
        case 'MD5':
            return md5($newPassword);
        case 'CRYPT':
            $salt = substr($newPassword, 0, 2);
            return crypt($newPassword, $salt);
        case 'PLAIN':
            return $newPassword;
        default:
            return $newPassword;
    }
}

?>
