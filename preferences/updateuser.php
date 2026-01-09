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
?>

    <div class="container-fluid">
        <?php if ($error != ""): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><?php echo $strings["edit_user_account"]; ?></h5>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["full_name"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="text" class="form-control" name="fn" value="<?php echo htmlspecialchars($userPrefs->mem_name[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["title"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="text" class="form-control" name="tit" value="<?php echo htmlspecialchars($userPrefs->mem_title[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["email"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="email" class="form-control" name="em" value="<?php echo htmlspecialchars($userPrefs->mem_email_work[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["work_phone"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="text" class="form-control" name="wp" value="<?php echo htmlspecialchars($userPrefs->mem_phone_work[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["home_phone"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="text" class="form-control" name="hp" value="<?php echo htmlspecialchars($userPrefs->mem_phone_home[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["mobile_phone"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="text" class="form-control" name="mp" value="<?php echo htmlspecialchars($userPrefs->mem_mobile[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["fax"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <input type="text" class="form-control" name="fax" value="<?php echo htmlspecialchars($userPrefs->mem_fax[0]); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["logout_time"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <select name="logout_time" class="form-select">
                                    <?php foreach ($autoLogoutOptions as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($userPrefs->mem_logout_time[0] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ($gmtTimezone == "true"): ?>
                            <div class="row mb-3">
                                <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["user_timezone"] . $blockPage->printHelp("user_timezone"); ?>:</label>
                                <div class="col-md-9 col-lg-10">
                                    <select name="tz" class="form-select">
                                        <?php for ($i = -12; $i <= 12; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($userPrefs->mem_timezone[0] == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["start_page"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <select name="start_page" class="form-select">
                                    <?php
                                    $displayOptions = $startPageOptions;
                                    if ($userPrefs->mem_profil[0] == 0) {
                                        $displayOptions = array_merge(array('administration/admin.php' => 'Administration page'), $startPageOptions);
                                    }
                                    foreach ($displayOptions as $key => $value):
                                        ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($userPrefs->mem_last_page[0] == $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["permissions"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <?php
                                $profilePermissions = [
                                    "0" => $strings["administrator_permissions"],
                                    "1" => $strings["project_manager_permissions"],
                                    "2" => $strings["user_permissions"],
                                    "5" => $strings["project_manager_administrator_permissions"]
                                ];
                                if (isset($profilePermissions[$userPrefs->mem_profil[0]])) {
                                    echo '<p class="form-control-plaintext">' . $profilePermissions[$userPrefs->mem_profil[0]] . '</p>';
                                }
                                ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-3 col-lg-2 col-form-label"><?php echo $strings["account_created"]; ?>:</label>
                            <div class="col-md-9 col-lg-10">
                                <p class="form-control-plaintext"><?php echo createDate($userPrefs->mem_created[0], $_SESSION['timezoneSession']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i><?php echo $strings["save"]; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!--
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3"><?php echo $strings["quick_links"]; ?></h6>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="../preferences/updatepassword.php" class="btn btn-outline-secondary">
                                <i class="fas fa-key me-1"></i><?php echo $strings["change_password"]; ?>
                            </a>
                            <?php if ($notifications == "true"): ?>
                                <a href="../preferences/updatenotifications.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-bell me-1"></i><?php echo $strings["notifications"]; ?>
                                </a>
                            <?php endif; ?>
                            <button type="button" onclick="window.open('../users/exportuser.php?id=<?php echo $_SESSION['idSession']; ?>', '_blank')" class="btn btn-outline-secondary">
                                <i class="fas fa-download me-1"></i><?php echo $strings["export"]; ?>
                            </button>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </div>
    </div>

<?php
$block1->closeContent();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");

?>