<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: editservice.php,v 1.6 2004/12/15 19:43:37 madbear Exp $
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
// case update user
if ($id != "") {
    // case update user
    if ($action == "update") {
        // replace quotes by html code in name and address
        $n = convertData($n);
        $np = convertData($np);
        $tmpquery = "UPDATE " . $tableCollab["services"] . " SET name='$n',name_print='$np',hourly_rate='$hr' WHERE id = '$id'";
        connectSql($tmpquery);
        header("Location: ../services/listservices.php?msg=update");
        exit;
    } 
    $tmpquery = "WHERE serv.id = '$id'";
    $detailService = new request();
    $detailService->openServices($tmpquery);
    $comptDetailService = count($detailService->serv_id);
    // set values in form
    $n = $detailService->serv_name[0];
    $np = $detailService->serv_name_print[0];
    $hr = $detailService->serv_hourly_rate[0];
} 
// case add user
if ($id == "") {
    if ($action == "add") {
        // replace quotes by html code in name and address
        $n = convertData($n);
        $np = convertData($np);
        $tmpquery1 = "INSERT INTO " . $tableCollab["services"] . " SET name='$n',name_print='$np',hourly_rate='$hr'";
        connectSql($tmpquery1);
        header("Location: ../services/listservices.php?msg=add");
        exit;
    } 
} 


//--- header ---
$breadcrumbs[]=buildLink("../administration/admin.php?", $strings["administration"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../services/listservices.php?", $strings["service_management"], LINK_INSIDE);

if ($id == "") {
    $breadcrumbs[]=$strings["add_service"];
}                                         
if ($id != "") {
    $breadcrumbs[]=buildLink("../services/viewservice.php?id=$id", $detailService->serv_name[0], LINK_INSIDE);
    $breadcrumbs[]=$strings["edit_service"];
} 

$bodyCommand = "onLoad=\"document.serv_editForm.n.focus();\"";
$pageSection = 'admin';
require_once("../themes/" . THEME . "/header.php");

//--- content ----
$block1 = new block();

if ($id == "") {
    $block1->form = "serv_edit";
    $block1->openForm("../services/editservice.php?id=$id&amp;action=add#" . $block1->form . "Anchor");
} 
if ($id != "") {
    $block1->form = "serv_edit";
    $block1->openForm("../services/editservice.php?id=$id&amp;action=update#" . $block1->form . "Anchor");
} 

if ($error != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
} 

if ($id == "") {
    $block1->headingForm($strings["add_service"]);
} 
else {
    $block1->headingForm($strings["edit_service"] . " : " . $detailService->serv_name[0]);
} 

$block1->openContent();

if ($id == "") {
    $block1->contentTitle($strings["details"]);
} 
if ($id != "") {
    $block1->contentTitle($strings["details"]);
} 

// ---------- NAME ----------
echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["name"] . ' :</label>
        <input type="text" name="n" value="' . htmlspecialchars($n) . '" class="form-control" >
      </div>';

// ---------- NAME PRINT ----------
echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["name_print"] . ' :</label>
        <input type="text" name="np" value="' . htmlspecialchars($np) . '" class="form-control" >
      </div>';

// ---------- HOURLY RATE ----------
echo '<div class="mb-3">
        <label class="form-label fw-bold">' . $strings["hourly_rate"] . ' :</label>
        <input type="text" name="hr" value="' . htmlspecialchars($hr) . '" class="form-control" >
      </div>';

// ---------- SUBMIT BUTTON ----------
echo '<div class="mb-3">
        <input type="submit" name="Save" value="' . $strings["save"] . '" class="btn btn-primary">
      </div>';


$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");

?>
