<?php // $Revision: 1.2 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: deleteholidays.php,v 1.2 2004/12/15 19:43:07 madbear Exp $
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

if ($_SESSION['profilSession'] != 0) {
    header('Location: ../general/permissiondenied.php');
    exit;
}

$breadcrumbs[]=buildLink('../administration/admin.php', $strings['administration'], LINK_INSIDE);
$breadcrumbs[]=$strings['holidays'];

$pageSection = 'admin';

// Add Bootstrap CSS and JS
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>';
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>';

require_once('../themes/' . THEME . '/header.php');

$block1 = new block();
$block1->headingForm($strings['delete_holidays']);

$block1->openContent();
$block1->contentTitle($strings["delete_following"]);

$block1->form = 'hoP';
$block1->openForm("../administration/listholidays.php?action=delete&amp;id=$id");

// Start Bootstrap content
echo '<div class="container mt-4">';
echo '<div class="card">';
echo '<div class="card-body">';

$id = str_replace("**", ",", $id);
$tmpquery = "WHERE hol.id IN($id) ORDER BY hol.comments";
$listHoliday = new request();
$listHoliday->openHoliday($tmpquery);
$comptListHoliday = count($listHoliday->hol_id);

if ($comptListHoliday > 0) {
    echo '<h5 class="card-title mb-4">' . $strings["delete_following"] . '</h5>';

    echo '<div class="alert alert-warning mb-4">';
    echo '<p class="mb-0">' . sprintf($strings["delete_holidays_confirm"], $comptListHoliday) . '</p>';
    echo '</div>';

    echo '<div class="list-group mb-4">';
    for ($i = 0;$i < $comptListHoliday;$i++) {
        echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
        echo '<div>';
        echo '<strong>#' . htmlspecialchars($listHoliday->hol_id[$i]) . '</strong> - ';
        echo htmlspecialchars($listHoliday->hol_comments[$i]);
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';

    echo '<div class="mt-4">';
    echo '<input type="submit" name="delete" value="' . $strings["delete"] . '" class="btn btn-danger mr-2">';
    echo '<input type="button" name="cancel" value="' . $strings["cancel"] . '" onClick="history.back();" class="btn btn-secondary">';
    echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
    echo '</div>';
}

echo '</div>';
echo '</div>';
echo '</div>';

$block1->closeForm();
$block1->closeContent();
$block1->headingForm_close();

require_once("../themes/" . THEME . "/footer.php");

?>