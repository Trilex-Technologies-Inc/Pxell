<?php // $Revision: 1.7 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: edituser.php,v 1.7 2004/12/15 19:43:40 madbear Exp $
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

$id = (string) ($_GET['id'] ?? '');
$action = (string) ($_GET['action'] ?? '');
$un = (string) ($_POST['un'] ?? '');
$unOld = (string) ($_POST['unOld'] ?? '');
$fn = (string) ($_POST['fn'] ?? '');
$tit = (string) ($_POST['tit'] ?? '');
$em = (string) ($_POST['em'] ?? '');
$wp = (string) ($_POST['wp'] ?? '');
$hp = (string) ($_POST['hp'] ?? '');
$mp = (string) ($_POST['mp'] ?? '');
$fax = (string) ($_POST['fax'] ?? '');
$c = (string) ($_POST['c'] ?? '');
$perm = (string) ($_POST['perm'] ?? '2');
$pw = (string) ($_POST['pw'] ?? '');
$pwa = (string) ($_POST['pwa'] ?? '');
$error = '';
$comptListProjects = 0;

if ($_SESSION['profilSession'] != "0") {
    header("Location: ../general/permissiondenied.php");
    exit;
}
// case update user
if ($id != "") {
    if ($id == "1" && $_SESSION['idSession'] == "1") {
        header("Location: ../preferences/updateuser.php");
        exit;
    }
    // case update user
    if ($action == "update") {
        if ($htaccessAuth == "true") {
            require_once("../includes/htpasswd.class.php");
            $Htpasswd = new Htpasswd;
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $un)) {
            $error = $strings["alpha_only"];
        } else {
            // test if login already exists
            $tmpquery = "WHERE mem.login = '$un' AND mem.login != '$unOld'";
            $existsUser = new request();
            $existsUser->openMembers($tmpquery);
            $comptExistsUser = count($existsUser->mem_id);
            if ($comptExistsUser != "0") {
                $error = $strings["user_already_exists"];
            } else {
                // replace quotes by html code in name and address
                $fn = convertData($fn);
                $tit = convertData($tit);
                $c = convertData($c);
                $em = convertData($em);
                $wp = convertData($wp);
                $hp = convertData($hp);
                $mp = convertData($mp);
                $fax = convertData($fax);

                $tmpquery = "UPDATE " . $tableCollab["members"] . " SET login='$un',name='$fn',title='$tit',email_work='$em',phone_work='$wp',phone_home='$hp',mobile='$mp',fax='$fax',comments='$c',profil='$perm' WHERE id = '$id'";
                connectSql("$tmpquery");

                if ($htaccessAuth == "true") {
                    if ($un != $unOld) {
                        $tmpquery = "WHERE tea.member = '$id'";
                        $listProjects = new request();
                        $listProjects->openTeams($tmpquery);
                        $comptListProjects = count($listProjects->tea_id);

                        if ($comptListProjects != "0") {
                            for ($i = 0;$i < $comptListProjects;$i++) {
                                $Htpasswd->initialize("../files/" . $listProjects->tea_pro_id[$i] . "/.htpasswd");
                                $Htpasswd->renameUser($unOld, $un);
                            }
                        }
                    }
                }
                // test if new password set
                if ($pw != "") {
                    // test if 2 passwords match
                    if ($pw != $pwa || $pwa == "") {
                        $error = $strings["new_password_error"];
                    } else {
                        $pw = get_password($pw);

                        if ($htaccessAuth == "true") {
                            if ($un == $unOld) {
                                $tmpquery = "WHERE tea.member = '$id'";
                                $listProjects = new request();
                                $listProjects->openTeams($tmpquery);
                                $comptListProjects = count($listProjects->tea_id);
                            }

                            if ($comptListProjects != "0") {
                                for ($i = 0;$i < $comptListProjects;$i++) {
                                    $Htpasswd->initialize("../files/" . $listProjects->tea_pro_id[$i] . "/.htpasswd");
                                    $Htpasswd->changePass($un, $pw);
                                }
                            }
                        }
                        $tmpquery = "UPDATE " . $tableCollab["members"] . " SET password='$pw' WHERE id = '$id'";
                        connectSql("$tmpquery");
                        // if mantis bug tracker enabled
                        if ($enableMantis == "true") {
                            // Call mantis function for user changes..!!!
                            $f_access_level = $team_user_level; // Developer
                            require_once ("../mantis/user_update.php");
                        }

                        header("Location: ../users/listusers.php?msg=update");
                        exit;
                    }
                } else {
                    // if mantis bug tracker enabled
                    if ($enableMantis == "true") {
                        // Call mantis function for user changes..!!!
                        $f_access_level = $team_user_level; // Developer
                        require_once ("../mantis/user_update.php");
                    }
                    header("Location: ../users/listusers.php?msg=update");
                    exit;
                }
            }
        }
    }
    $tmpquery = "WHERE mem.id = '$id'";
    $detailUser = new request();
    $detailUser->openMembers($tmpquery);
    $comptDetailUser = count($detailUser->mem_id);
    // test exists selected user, redirect to list if not
    if ($comptDetailUser == "0") {
        header("Location: ../users/listusers.php?msg=blankUser");
        exit;
    }
    // set values in form
    $un = $detailUser->mem_login[0];
    $fn = $detailUser->mem_name[0];
    $tit = $detailUser->mem_title[0];

    $em = $detailUser->mem_email_work[0];
    $wp = $detailUser->mem_phone_work[0];
    $hp = $detailUser->mem_phone_home[0];
    $mp = $detailUser->mem_mobile[0];
    $fax = $detailUser->mem_fax[0];
    $c = $detailUser->mem_comments[0];
    $perm = $detailUser->mem_profil[0];
    // set radio button with permissions value
    if ($perm == "1") {
        $checked1 = "checked";
    }
    if ($perm == "2") {
        $checked2 = "checked";
    }
    if ($perm == "4") {
        $checked4 = "checked";
    }
    if ($perm == "5") {
        $checked5 = "checked";
    }
}
// case add user
if ($id == "") {
    $checked2 = "checked";
    // case add user
    if ($action == "add") {
        if (!preg_match('/^[A-Za-z0-9]+$/', $un)) {
            $error = $strings["alpha_only"];
        } else {
            // test if login already exists
            $tmpquery = "WHERE mem.login = '$un'";
            $existsUser = new request();
            $existsUser->openMembers($tmpquery);
            $comptExistsUser = count($existsUser->mem_id);
            if ($comptExistsUser != "0") {
                $error = $strings["user_already_exists"];
            } else {
                // test if 2 passwords match
                if ($pw != $pwa || $pw == "") {
                    $error = $strings["new_password_error"];
                } else {
                    // replace quotes by html code in name and address
                    $fn = convertData($fn);
                    $tit = convertData($tit);
                    $c = convertData($c);
                    $pw = get_password($pw);
                    $tmpquery1 = "INSERT INTO " . $tableCollab["members"] . "(login,name,title,email_work,phone_work,phone_home,mobile,fax,comments,password,profil,created,organization,timezone) VALUES('$un','$fn','$tit','$em','$wp','$hp','$mp','$fax','$c','$pw','$perm','$dateheure','1','0')";
                    connectSql("$tmpquery1");
                    $tmpquery = $tableCollab["members"];
                    last_id($tmpquery);
                    $num = $lastId[0];
                    unset($lastId);
                    $tmpquery2 = "INSERT INTO " . $tableCollab["sorting"] . "(member) VALUES('$num')";
                    connectSql("$tmpquery2");
                    $tmpquery3 = "INSERT INTO " . $tableCollab["notifications"] . "(member,taskAssignment,removeProjectTeam,addProjectTeam,newTopic,newPost,statusTaskChange,priorityTaskChange,duedateTaskChange,clientAddTask) VALUES ('$num','0','0','0','0','0','0','0','0','0')";
                    connectSql("$tmpquery3");
                    // if mantis bug tracker enabled
                    if ($enableMantis == "true") {
                        // Call mantis function for user changes..!!!
                        $f_access_level = $team_user_level; // Developer
                        require_once ("../mantis/create_new_user.php");
                    }
                    header("Location: ../users/listusers.php?msg=add");
                    exit;
                }
            }
        }
    }
}


//--- header----
$breadcrumbs[]= buildLink("../administration/admin.php?", $strings["administration"], LINK_INSIDE);
$breadcrumbs[]= buildLink("../users/listusers.php?", $strings["user_management"], LINK_INSIDE);

if ($id == "") {
    $breadcrumbs[]=$strings["add_user"];
}
else {
    $breadcrumbs[]=buildLink("../users/viewuser.php?id=$id", $detailUser->mem_login[0], LINK_INSIDE);
    $breadcrumbs[]=$strings["edit_user"];
}


$bodyCommand = "onLoad=\"document.user_editForm.un.focus();\"";
require_once("../themes/" . THEME . "/header.php");

//---- content -----
$block1 = new block();

if ($id == "") {
    $block1->form = "user_edit";
    $block1->openForm("../users/edituser.php?id=$id&amp;action=add#" . $block1->form . "Anchor");
}
if ($id != "") {
    $block1->form = "user_edit";
    $block1->openForm("../users/edituser.php?id=$id&amp;action=update#" . $block1->form . "Anchor");
}

if ($error != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
}

if ($id == "") {
    $block1->headingForm($strings["add_user"]);
}
if ($id != "") {
    $block1->headingForm($strings["edit_user"] . " : " . $detailUser->mem_login[0]);
}
function checked_if($value, $default = '') {
    return isset($value) ? $value : $default;
}
$block1->openContent();
?>
    <div class="container mt-4">
        <?php if ($error != ""): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if ($id == ""): ?>
                    <h5 class="card-title"><?php echo $strings["enter_user_details"]; ?></h5>
                <?php else: ?>
                    <h5 class="card-title"><?php echo $strings["edit_user_details"]; ?></h5>
                <?php endif; ?>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["user_name"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="un" value="<?php echo htmlspecialchars($un); ?>" maxlength="16" autofocus>
                        <input type="hidden" name="unOld" value="<?php echo htmlspecialchars($un); ?>">

                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["full_name"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="fn" value="<?php echo htmlspecialchars($fn); ?>" maxlength="64">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["title"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="tit" value="<?php echo htmlspecialchars($tit); ?>" maxlength="128">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["email"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" name="em" value="<?php echo htmlspecialchars($em); ?>" maxlength="128">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["work_phone"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="wp" value="<?php echo htmlspecialchars($wp); ?>" maxlength="32">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["home_phone"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="hp" value="<?php echo htmlspecialchars($hp); ?>" maxlength="32">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["mobile_phone"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="mp" value="<?php echo htmlspecialchars($mp); ?>" maxlength="32">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["fax"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="fax" value="<?php echo htmlspecialchars($fax); ?>" maxlength="32">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?> :</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="c" rows="4"><?php echo htmlspecialchars($c); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <?php if ($id == ""): ?>
                    <h5 class="card-title"><?php echo $strings["enter_password"]; ?></h5>
                <?php else: ?>
                    <h5 class="card-title"><?php echo $strings["change_password_user"]; ?></h5>
                    <p class="text-muted"><?php echo $strings["leave_blank_password"]; ?></p>
                <?php endif; ?>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["password"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" name="pw" value="" maxlength="15">
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["confirm_password"]; ?> :</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" name="pwa" value="" maxlength="16">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title"><?php echo $strings["select_permissions"]; ?></h5>

                <div class="row mb-3">
                    <div class="col-sm-9 offset-sm-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="perm" value="1" id="perm1" <?php echo (isset($checked1) && !is_null($checked1)) ? $checked1 : '';
                            ?>>
                            <label class="form-check-label" for="perm1">
                                <strong><?php echo $strings["project_manager_permissions"]; ?></strong>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="perm" value="2" id="perm2" <?php echo checked_if($checked2); ?>>
                            <label class="form-check-label" for="perm2">
                                <strong><?php echo $strings["user_permissions"]; ?></strong>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="perm" value="4" id="perm4" <?php echo checked_if($checked4); ?>>
                            <label class="form-check-label" for="perm4">
                                <strong><?php echo $strings["disabled_permissions"]; ?></strong>
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="perm" value="5" id="perm5" <?php echo checked_if($checked5); ?>>
                            <label class="form-check-label" for="perm5">
                                <strong><?php echo $strings["project_manager_administrator_permissions"]; ?></strong>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary"><?php echo $strings["save"]; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");

?>
