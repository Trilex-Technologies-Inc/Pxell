<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: updateuser.php,v 1.5 2004/12/13 00:18:25 madbear Exp $
 *
 * Copyright (c) 2003 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$checkSession = true;
require_once("../includes/library.php");

if ($action == "update") {
    if (($logout_time < "30" && $logout_time != "0") || !is_numeric($logout_time)) {
        $logout_time = "30";
    }

    $fn = convertData($fn);
    $tit = convertData($tit);
    $em = convertData($em);
    $wp = convertData($wp);
    $hp = convertData($hp);
    $mp = convertData($mp);
    $fax = convertData($fax);
    $logout_time = convertData($logout_time);
    $start_page = convertData($start_page);

    $tmpquery = "UPDATE " . $tableCollab["members"] . " SET name='$fn',title='$tit',email_work='$em',phone_work='$wp',phone_home='$hp',mobile='$mp',fax='$fax',logout_time='$logout_time',timezone='$tz',last_page='$start_page' WHERE id = '" . $_SESSION['idSession'] . "'";

    connectSql($tmpquery);

    // save to the session
    $_SESSION['logouttimeSession'] = $logout_time;
    $_SESSION['timezoneSession'] = $tz;
    $_SESSION['dateunixSession'] = date("U");
    $_SESSION['nameSession'] = $fn;

    // if mantis bug tracker enabled
    if ($enableMantis == "true") {
        // Call mantis function for user profile changes..!!!
        require_once ("../mantis/user_profile.php");
    }

    header("Location: ../preferences/updateuser.php?msg=update");
    exit;
}

$tmpquery = "WHERE mem.id = '" . $_SESSION['idSession'] . "'";
$userPrefs = new request();
$userPrefs->openMembers($tmpquery);
$comptUserPrefs = count($userPrefs->mem_id);

if ($comptUserPrefs == "0") {
    header("Location: ../users/listusers.php?msg=blankUser");
    exit;
}



//--- header ---
$breadcrumbs[]=$strings["preferences"];
if ($notifications == "true") {
    $breadcrumbs[]=$strings["user_profile"] . "&nbsp; | &nbsp;" . buildLink("../preferences/updatepassword.php?", $strings["change_password"], LINK_INSIDE) . "&nbsp; | &nbsp;" . buildLink("../preferences/updatenotifications.php?", $strings["notifications"], LINK_INSIDE);
} else {
    $breadcrumbs[]=$strings["user_profile"] . "&nbsp; | &nbsp;" . buildLink("../preferences/updatepassword.php?", $strings["change_password"], LINK_INSIDE);
}



$bodyCommand = "onLoad=\"document.user_edit_profileForm.fn.focus();\"";
$pageSection = 'preferences';
require_once("../themes/" . THEME . "/header.php");

//--- content -------
$blockPage= new block();

$block1 = new block();

$block1->form = "user_edit_profile";
$block1->openForm("../preferences/updateuser.php");
echo "<input type=\"hidden\" name=\"action\" value=\"update\">";

if ($error != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
}

$block1->heading($strings["user_profile"] . " : " . $userPrefs->mem_login[0]);

$block1->openPaletteIcon();
$block1->paletteIcon(0, "export", $strings["export"]);
$block1->closePaletteIcon();

$block1->openContent();
$block1->contentTitle($strings["edit_user_account"]);


// ---------- FULL NAME ----------
$block1->formRow(
    $strings["full_name"],
    '<input type="text" name="fn" value="' . htmlspecialchars($userPrefs->mem_name[0]) . '" class="form-control" >'
);

// ---------- TITLE ----------
$block1->formRow(
    $strings["title"],
    '<input type="text" name="tit" value="' . htmlspecialchars($userPrefs->mem_title[0]) . '" class="form-control" >'
);

// ---------- EMAIL ----------
$block1->formRow(
    $strings["email"],
    '<input type="email" name="em" value="' . htmlspecialchars($userPrefs->mem_email_work[0]) . '" class="form-control" >'
);

// ---------- WORK PHONE ----------
$block1->formRow(
    $strings["work_phone"],
    '<input type="text" name="wp" value="' . htmlspecialchars($userPrefs->mem_phone_work[0]) . '" class="form-control" >'
);

// ---------- HOME PHONE ----------
$block1->formRow(
    $strings["home_phone"],
    '<input type="text" name="hp" value="' . htmlspecialchars($userPrefs->mem_phone_home[0]) . '" class="form-control" >'
);

// ---------- MOBILE PHONE ----------
$block1->formRow(
    $strings["mobile_phone"],
    '<input type="text" name="mp" value="' . htmlspecialchars($userPrefs->mem_mobile[0]) . '" class="form-control" >'
);

// ---------- FAX ----------
$block1->formRow(
    $strings["fax"],
    '<input type="text" name="fax" value="' . htmlspecialchars($userPrefs->mem_fax[0]) . '" class="form-control" >'
);

// ---------- LOGOUT TIME SELECT ----------
$logoutMenu = '<select name="logout_time" class="form-select" >';
foreach ($autoLogoutOptions as $key => $value) {
    $selected = ($userPrefs->mem_logout_time[0] == $key) ? 'selected' : '';
    $logoutMenu .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
}
$logoutMenu .= '</select>';
$block1->formRow($strings['logout_time'], $logoutMenu);

// ---------- TIMEZONE SELECT ----------
if ($gmtTimezone == "true") {
    $selectTimezone = '<select name="tz" class="form-select" >';
    for ($i = -12; $i <= 12; $i++) {
        $selected = ($userPrefs->mem_timezone[0] == $i) ? 'selected' : '';
        $selectTimezone .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
    }
    $selectTimezone .= '</select>';
    $block1->formRow($strings["user_timezone"] . $blockPage->printHelp("user_timezone"), $selectTimezone);
}

// ---------- START PAGE SELECT ----------
$startPageMenu = '<select name="start_page" class="form-select" style="max-width: 300px;">';
if ($userPrefs->mem_profil[0] == 0) {
    $startPageOptions = array_merge(array('administration/admin.php' => 'Administration page'), $startPageOptions);
}
foreach ($startPageOptions as $key => $value) {
    $selected = ($userPrefs->mem_last_page[0] == $key) ? 'selected' : '';
    $startPageMenu .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
}
$startPageMenu .= '</select>';
$block1->formRow($strings['start_page'], $startPageMenu);

// ---------- PERMISSIONS ----------
$profilePermissions = [
    "0" => $strings["administrator_permissions"],
    "1" => $strings["project_manager_permissions"],
    "2" => $strings["user_permissions"],
    "5" => $strings["project_manager_administrator_permissions"]
];
if (isset($profilePermissions[$userPrefs->mem_profil[0]])) {
    $block1->formRow($strings["permissions"], $profilePermissions[$userPrefs->mem_profil[0]]);
}

// ---------- ACCOUNT CREATED ----------
$block1->formRow($strings["account_created"], createDate($userPrefs->mem_created[0], $_SESSION['timezoneSession']));

// ---------- SUBMIT BUTTON ----------
$block1->formRow(
    "",
    '<input type="submit" name="Save" value="' . $strings["save"] . '" class="btn btn-primary">'
);


$block1->closeContent();
$block1->closeForm();

$block1->openPaletteScript();
$block1->paletteScript(0, "export", "../users/exportuser.php?id=" . $_SESSION['idSession'], "true,true,true", $strings["export"]);
$block1->closePaletteScript("", "");

require_once("../themes/" . THEME . "/footer.php");

?>
