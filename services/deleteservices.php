<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: deleteservices.php,v 1.6 2004/12/15 19:43:37 madbear Exp $
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

if ($_SESSION['profilSession'] != "0") {
    header("Location: ../general/permissiondenied.php");
    exit;
}

if ($action == "delete") {
    $id = str_replace("**", ",", $id);
    $tmpquery1 = "DELETE FROM " . $tableCollab["services"] . " WHERE id IN($id)";
    connectSql($tmpquery1);
    header("Location: ../services/listservices.php?msg=delete");
    exit;
}



//--- header ---
$breadcrumbs[]=buildLink("../administration/admin.php?", $strings["administration"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../services/listservices.php?", $strings["service_management"], LINK_INSIDE);
$breadcrumbs[]=$strings["delete_services"];

$pageSection = 'admin';
require_once("../themes/" . THEME . "/header.php");

//--- content ---
$block1 = new block();

$block1->form = "service_delete";
$block1->openForm("../services/deleteservices.php?action=delete");

$block1->headingForm($strings["delete_services"]);

$block1->openContent();
$block1->contentTitle($strings["delete_following"]);

echo '<div class="container mt-4">';
echo '<div class="card">';
echo '<div class="card-body">';

$id = str_replace("**", ",", $id);
$tmpquery = "WHERE serv.id IN($id) ORDER BY serv.name";
$listServices = new request();
$listServices->openServices($tmpquery);
$comptListServices = count($listServices->serv_id);

if ($comptListServices > 0) {
    echo '<h5 class="card-title mb-3">' . $strings["delete_following"] . '</h5>';

    echo '<div class="alert alert-warning mb-4">';
    echo '<p class="mb-0">You are about to delete ' . $comptListServices . ' service(s). This action cannot be undone.</p>';
    echo '</div>';

    echo '<div class="list-group mb-4">';
    for ($i = 0;$i < $comptListServices;$i++) {
        echo '<div class="list-group-item">';
        echo '<strong>' . htmlspecialchars($listServices->serv_name[$i]) . '</strong>';
        if (!empty($listServices->serv_name_print[$i])) {
            echo ' (' . htmlspecialchars($listServices->serv_name_print[$i]) . ')';
        }
        echo '</div>';
    }
    echo '</div>';

    echo '<form method="post" action="../services/deleteservices.php?action=delete">';
    echo '<div class="mt-4">';
    echo '<input type="submit" name="delete" value="' . $strings["delete"] . '" class="btn btn-danger mr-2">';
    echo '<input type="button" name="cancel" value="' . $strings["cancel"] . '" onClick="history.back();" class="btn btn-secondary">';
    echo '<input type="hidden" value="' . htmlspecialchars($id) . '" name="id">';
    echo '</div>';
    echo '</form>';
}

echo '</div>';
echo '</div>';
echo '</div>';

$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");

?>