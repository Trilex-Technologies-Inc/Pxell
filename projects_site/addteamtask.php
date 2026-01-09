<?php // $Revision: 1.4 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: addteamtask.php,v 1.4 2005/01/04 06:40:43 luiswang Exp $
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
// case add task
if ($id == "") {
    // case add task
    if ($action == "add") {
        // concat values from date selector and replace quotes by html code in name
        $tn = convertData($tn);
        $d = convertData($d);
        $c = convertData($c);

        $tmpquery1 = "INSERT INTO " . $tableCollab["tasks"] . "(project,name,description,owner,assigned_to,status,priority,start_date,due_date,estimated_time,actual_time,comments,created,published,completion,milestone) VALUES('" . $_SESSION['projectSession'] . "','$tn','$d','" . $_SESSION['idSession'] . "','0','2','$pr','$sd','$dd','$etm','$atm','$c','$dateheure','$pub','0','$miles')";
        connectSql("$tmpquery1");
        $tmpquery = $tableCollab["tasks"];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);

        $tmpquery2 = "INSERT INTO " . $tableCollab["assignments"] . "(task,owner,assigned_to,assigned) VALUES('$num','" . $_SESSION['idSession'] . "','$at','$dateheure')";
        connectSql("$tmpquery2");
        // send task assignment mail if notifications = true
        if ($notifications == "true") {
            require_once("../tasks/noti_clientaddtask.php");
        } 
        // create task sub-folder if filemanagement = true
        if ($fileManagement == "true") {
            createDir("../files/" . $_SESSION['projectSession'] . "/$num");
        } 
        header('Location: showallteamtasks.php');
        exit;
    } 
} 

$bodyCommand = "onload=\"document.etDForm.tn.focus();\"";

$bouton[2] = "over";
$titlePage = $strings["add_task"];
require_once ("include_header.php");

echo '<form accept-charset="UNKNOWN" method="POST" action="../projects_site/addteamtask.php?project=' . $_SESSION['projectSession'] . '&amp;action=add#etDAnchor" name="etDForm" enctype="application/x-www-form-urlencoded" class="row g-3">
<div class="col-md-8">
<h3>' . htmlspecialchars($strings["add_task"]) . '</h3>
<input type="hidden" name="owner" value="' . $projectDetail->pro_owner[0] . '">
<input type="hidden" name="at" value="0">
<input type="hidden" name="st" value="2">
<input type="hidden" name="completion" value="0">
<input type="hidden" value="1" name="pub">
<input type="hidden" value="1" name="miles">
<div class="mb-3">
<label for="tn" class="form-label">* ' . htmlspecialchars($strings["name"]) . ':</label>
<input type="text" class="form-control" id="tn" name="tn" value="' . htmlspecialchars($tn) . '" maxlength="100" required>
</div>
<div class="mb-3">
<label for="d" class="form-label">' . htmlspecialchars($strings["description"]) . ':</label>
<textarea class="form-control" id="d" name="d" rows="6">' . htmlspecialchars($d) . '</textarea>
</div>
<div class="mb-3">
<label for="pr" class="form-label">' . htmlspecialchars($strings["priority"]) . ':</label>
<select class="form-select" name="pr" id="pr">';

$comptPri = count($priority);

for ($i = 0;$i < $comptPri;$i++) {
    if ($taskDetail->tas_priority[0] == $i) {
        echo '<option value="' . $i . '" selected>' . htmlspecialchars($priority[$i]) . '</option>';
    } else {
        echo '<option value="' . $i . '">' . htmlspecialchars($priority[$i]) . '</option>';
    } 
}

echo '</select>
</div>';

if ($sd == "") {
    $sd = $date;
} 
if ($dd == "") {
    $dd = "--";
} 

echo '<div class="mb-3">
<label for="sel1" class="form-label">' . htmlspecialchars($strings["start_date"]) . ':</label>
<div class="input-group">
<input type="text" class="form-control" id="sel1" name="sd" size="20" value="' . htmlspecialchars($sd) . '" style="max-width: 200px;">
<button type="button" class="btn btn-outline-secondary" id="trigger_a">...</button>
</div>
<script type="text/javascript">Calendar.setup({ inputField:"sel1", button:"trigger_a" });</script>
</div>
<div class="mb-3">
<label for="sel3" class="form-label">' . htmlspecialchars($strings["due_date"]) . ':</label>
<div class="input-group">
<input type="text" class="form-control" id="sel3" name="dd" size="20" value="' . htmlspecialchars($dd) . '" style="max-width: 200px;">
<button type="button" class="btn btn-outline-secondary" id="trigger_b">...</button>
</div>
<script type="text/javascript">Calendar.setup({ inputField:"sel3", button:"trigger_b" });</script>
</div>
<div class="mb-3">
<label for="c" class="form-label">' . htmlspecialchars($strings["comments"]) . ':</label>
<textarea class="form-control" id="c" name="c" rows="6">' . htmlspecialchars($c) . '</textarea>
</div>
<div class="mb-3">
<button type="submit" class="btn btn-primary">' . htmlspecialchars($strings["save"]) . '</button>
</div>
</div>
</form>
<p class="alert alert-info">' . htmlspecialchars($strings["client_add_task_note"]) . '</p>';

require_once ("include_footer.php");

?>
