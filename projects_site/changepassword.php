<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: changepassword.php,v 1.5 2005/05/28 21:14:02 madbear Exp $
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

if ($enable_cvs == "true") {
    require_once("../includes/cvslib.php");
} 

if ($action == "update") {
    // encrypt the old password using the defined loginMethod
    $opw = get_password($opw);

    if ($opw != $_SESSION['passwordSession']) {
        $error = $strings["old_password_error"];
    } else {
        if ($npw != $pwa || $npw == "") {
            $error = $strings["new_password_error"];
        } else {
            $cnpw = get_password($npw);

            if ($htaccessAuth == "true") {
                include_once("includes/htpasswd.class.php");
                $Htpasswd = new Htpasswd;
                $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "'";
                $listProjects = new request();
                $listProjects->openTeams($tmpquery);
                $comptListProjects = count($listProjects->tea_id);

                if ($comptListProjects != "0") {
                    for ($i = 0;$i < $comptListProjects;$i++) {
                        $Htpasswd->initialize("files/" . $listProjects->tea_pro_id[$i] . "/.htpasswd");
                        $Htpasswd->changePass($_SESSION['loginSession'], $cnpw);
                    } 
                } 
            } 

            $tmpquery = "UPDATE " . $tableCollab["members"] . " SET password='$cnpw' WHERE id = '" . $_SESSION['idSession'] . "'";
            connectSql("$tmpquery");
            // if CVS repository enabled
            if ($enable_cvs == "true") {
                $query = "WHERE tea.member = '" .$_SESSION['idSession'] . "'";
                $cvsMembers = new request();
                $cvsMembers->openTeams($query);
                // change the password in every repository
                for ($i = 0;$i < (count($cvsMembers->tea_id));$i++) {
                    cvs_change_password($cvsMembers->tea_mem_login[$i], $cnpw, $cvsMembers->tea_pro_id[$i]);
                } 
            } 

            // encrypt the new password using the defined loginMethod
            $npw = get_password($npw);

            $_SESSION['passwordSession'] = $npw;
            header('Location: changepassword.php?msg=update');
            exit;
        } 
    } 
} 

$tmpquery = "WHERE mem.id = '" . $_SESSION['idSession'] . "'";
$userDetail = new request();
$userDetail->openMembers($tmpquery);
$comptUserDetail = count($userDetail->mem_id);

if ($comptUserDetail == "0") {
    header('Location: userlist.php?msg=blankUser');
    exit;
} 

$titlePage = $strings["change_password"];
require_once("include_header.php");

echo '<form accept-charset="UNKNOWN" method="POST" action="../projects_site/changepassword.php?action=update" name="changepassword" enctype="application/x-www-form-urlencoded" class="row g-3">
<div class="col-md-6">
<h3>' . htmlspecialchars($strings["change_password"]) . '</h3>
<div class="mb-3">
<label for="opw" class="form-label">* ' . htmlspecialchars($strings["old_password"]) . ':</label>
<input type="password" class="form-control" id="opw" name="opw" value="" style="max-width: 300px;">
</div>
<div class="mb-3">
<label for="npw" class="form-label">* ' . htmlspecialchars($strings["new_password"]) . ':</label>
<input type="password" class="form-control" id="npw" name="npw" value="" style="max-width: 300px;">
</div>
<div class="mb-3">
<label for="pwa" class="form-label">* ' . htmlspecialchars($strings["confirm_password"]) . ':</label>
<input type="password" class="form-control" id="pwa" name="pwa" value="" style="max-width: 300px;">
</div>
<div class="mb-3">
<button type="submit" name="submit" class="btn btn-primary">' . htmlspecialchars($strings["save"]) . '</button>
</div>';
if ($error) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
}
echo '</div>
</form>';

require_once("include_footer.php");

?>
