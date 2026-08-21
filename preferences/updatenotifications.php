<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: updatenotifications.php,v 1.6 2004/12/15 12:25:22 pixtur Exp $
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

$tmpquery = "WHERE mem.id = '" . $_SESSION['idSession'] . "'";
$userPrefs = new request();
$userPrefs->openMembers($tmpquery);
$comptUserPrefs = count($userPrefs->mem_id);

if ($comptUserPrefs == "0") {
    header("Location: ../users/listusers.php?msg=blankUser");
    exit;
} 

if ($action == "update") {
    for ($i = 0;$i < 15;$i++) {
        if ($tbl_check[$i] == "") {
            $tbl_check[$i] = "1";
        } 
    } 

    $tmpquery = "UPDATE " . $tableCollab["notifications"] . " SET taskAssignment='$tbl_check[0]',statusTaskChange='$tbl_check[1]',priorityTaskChange='$tbl_check[2]',duedateTaskChange='$tbl_check[3]',addProjectTeam='$tbl_check[4]',removeProjectTeam='$tbl_check[5]',newPost='$tbl_check[6]',newTopic='$tbl_check[7]' WHERE member = '" . $_SESSION['idSession'] . "'";
    connectSql($tmpquery);

    header("Location: ../preferences/updatenotifications.php?msg=update");
    exit;
} 

$tmpquery = "WHERE noti.member = '" . $_SESSION['idSession'] . "'";
$userAvert = new request();
$userAvert->openNotifications($tmpquery);
if ($userAvert->not_taskassignment[0] == "0") {
    $taskAssignment = "checked";
} 
if ($userAvert->not_statustaskchange[0] == "0") {
    $statusTaskChange = "checked";
} 
if ($userAvert->not_prioritytaskchange[0] == "0") {
    $priorityTaskChange = "checked";
} 
if ($userAvert->not_duedatetaskchange[0] == "0") {
    $duedateTaskChange = "checked";
} 
if ($userAvert->not_addprojectteam[0] == "0") {
    $addProjectTeam = "checked";
} 
if ($userAvert->not_removeprojectteam[0] == "0") {
    $removeProjectTeam = "checked";
} 
if ($userAvert->not_newpost[0] == "0") {
    $newPost = "checked";
} 
if ($userAvert->not_newtopic[0] == "0") {
    $newTopic = "checked";
} 

$headBonus = "<script type=\"text/JavaScript\">
<!--
function checkboxes(){
	for (var i = 0; i < document.user_avertForm.elements.length; i++) {
		var e = document.user_avertForm.elements[i];
			if (e.type=='checkbox') {
				if (document.user_avertForm.chkbox_slt.value == \"true\") {
					e.checked = true;

				} else {
					e.checked = false;
				}
			}
	}
	if (document.user_avertForm.chkbox_slt.value == \"true\" ) {
		document.user_avertForm.chkbox_slt.value = \"false\";
	} else {
		document.user_avertForm.chkbox_slt.value = \"true\";
	}

}
//-->
</script>";



//--- header ---
$breadcrumbs[]=$strings["preferences"];
$breadcrumbs[]=buildLink("../preferences/updateuser.php?", $strings["user_profile"], LINK_INSIDE) . "&nbsp; | &nbsp;" . buildLink("../preferences/updatepassword.php?", $strings["change_password"], LINK_INSIDE) . "&nbsp; | &nbsp;" . $strings["notifications"];

$pageSection = 'preferences';
require_once("../themes/" . THEME . "/header.php");

//---content -----
$notificationOptions = array(
    array("0", $strings["edit_noti_taskassignment"], "fa-list-check", $taskAssignment),
    array("1", $strings["edit_noti_statustaskchange"], "fa-arrows-rotate", $statusTaskChange),
    array("2", $strings["edit_noti_prioritytaskchange"], "fa-flag", $priorityTaskChange),
    array("3", $strings["edit_noti_duedatetaskchange"], "fa-calendar-day", $duedateTaskChange),
    array("4", $strings["edit_noti_addprojectteam"], "fa-user-plus", $addProjectTeam),
    array("5", $strings["edit_noti_removeprojectteam"], "fa-user-minus", $removeProjectTeam),
    array("6", $strings["edit_noti_newpost"], "fa-message", $newPost),
    array("7", $strings["edit_noti_newtopic"], "fa-comments", $newTopic)
);
?>

<style>
    .notifications-page {
        display: grid;
        gap: 22px;
    }

    .notifications-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 22px;
        align-items: center;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
        padding: 24px;
    }

    .notifications-hero__eyebrow {
        color: #2f6f6a;
        font-size: 0.78rem;
        font-weight: 750;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .notifications-hero h1 {
        color: #162033;
        font-size: 1.9rem;
        font-weight: 750;
        letter-spacing: 0;
        margin: 0 0 8px;
    }

    .notifications-hero p {
        color: #657487;
        margin: 0;
    }

    .notifications-hero__badge {
        width: 72px;
        height: 72px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #e8f4f4;
        color: #2f6f6a;
        font-size: 1.8rem;
    }

    .notifications-tabs {
        display: inline-flex;
        gap: 6px;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(34, 49, 72, 0.06);
        padding: 6px;
        width: fit-content;
    }

    .notifications-tab {
        border-radius: 6px;
        color: #526174;
        font-weight: 750;
        padding: 9px 12px;
        text-decoration: none;
    }

    .notifications-tab:hover {
        color: #164773;
        text-decoration: none;
    }

    .notifications-tab--active {
        background: #164773;
        color: #ffffff;
    }

    .notifications-tab--active:hover {
        color: #ffffff;
    }

    .notifications-card {
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
        padding: 20px;
    }

    .notifications-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #d9e3ec;
        margin-bottom: 16px;
        padding-bottom: 16px;
    }

    .notifications-toolbar__user {
        color: #526174;
        font-weight: 700;
    }

    .notifications-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 12px;
    }

    .notification-option {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 12px;
        align-items: center;
        background: #f8fbfd;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        min-height: 82px;
        padding: 14px;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    }

    .notification-option:hover {
        border-color: #b7c6d5;
        box-shadow: 0 12px 28px rgba(34, 49, 72, 0.08);
        transform: translateY(-1px);
    }

    .notification-option__icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #e8f4f4;
        color: #2f6f6a;
    }

    .notification-option__label {
        color: #162033;
        font-weight: 700;
        line-height: 1.35;
    }

    .notification-option .form-check-input {
        width: 2.6rem;
        height: 1.35rem;
        cursor: pointer;
    }

    .notifications-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .notifications-actions .btn {
        min-height: 44px;
        padding-left: 22px;
        padding-right: 22px;
    }

    @media (max-width: 720px) {
        .notifications-hero {
            grid-template-columns: 1fr;
            padding: 20px;
        }

        .notifications-hero__badge {
            width: 56px;
            height: 56px;
            font-size: 1.35rem;
        }

        .notifications-tabs {
            display: grid;
            width: 100%;
        }
    }
</style>

<div class="notifications-page">
    <section class="notifications-hero">
        <div>
            <div class="notifications-hero__eyebrow"><?php echo $strings["preferences"]; ?></div>
            <h1><?php echo $strings["edit_notifications"]; ?></h1>
            <p><?php echo $strings["edit_notifications_info"]; ?></p>
        </div>
        <div class="notifications-hero__badge" aria-hidden="true">
            <i class="fa fa-bell"></i>
        </div>
    </section>

    <nav class="notifications-tabs" aria-label="Preference sections">
        <a class="notifications-tab" href="../preferences/updateuser.php"><?php echo $strings["user_profile"]; ?></a>
        <a class="notifications-tab" href="../preferences/updatepassword.php"><?php echo $strings["change_password"]; ?></a>
        <a class="notifications-tab notifications-tab--active" href="../preferences/updatenotifications.php"><?php echo $strings["notifications"]; ?></a>
    </nav>

    <?php if ($error != "") { ?>
        <div class="alert alert-danger" role="alert">
            <strong><?php echo $strings["errors"]; ?></strong><br>
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST" action="../preferences/updatenotifications.php?action=update" name="user_avertForm" class="notifications-card">
        <input type="hidden" name="chkbox_slt" value="true">

        <div class="notifications-toolbar">
            <div class="notifications-toolbar__user">
                <?php echo htmlspecialchars($userPrefs->mem_login[0]); ?>
            </div>
            <button type="button" class="btn btn-outline-primary" onclick="checkboxes();">
                <i class="fa fa-check-double me-1"></i><?php echo $strings["select_deselect"]; ?>
            </button>
        </div>

        <div class="notifications-grid">
            <?php foreach ($notificationOptions as $option) { ?>
                <label class="notification-option" for="notification_<?php echo $option[0]; ?>">
                    <span class="notification-option__icon" aria-hidden="true"><i class="fa <?php echo $option[2]; ?>"></i></span>
                    <span class="notification-option__label"><?php echo $option[1]; ?></span>
                    <input class="form-check-input" type="checkbox" name="tbl_check[<?php echo $option[0]; ?>]" value="0" id="notification_<?php echo $option[0]; ?>" <?php echo $option[3]; ?>>
                </label>
            <?php } ?>
        </div>

        <div class="notifications-actions">
            <input type="submit" name="Save" value="<?php echo $strings["save"]; ?>" class="btn btn-primary">
        </div>
    </form>
</div>
<?php

require_once("../themes/" . THEME . "/footer.php");

?>
