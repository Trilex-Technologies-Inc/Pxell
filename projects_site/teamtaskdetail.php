<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: teamtaskdetail.php,v 1.6 2005/05/18 03:47:14 vjack Exp $
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

$tmpquery = "WHERE tas.id = '$id'";
$taskDetail = new request();
$taskDetail->openTasks($tmpquery);

if ($taskDetail->tas_published[0] == "1" || $taskDetail->tas_project[0] != $_SESSION['projectSession']) {
    header('Location: index.php');
    exit;
} 

$bouton[2] = "over";
$titlePage = $strings["team_task_details"];
require_once ("include_header.php");

$block1 = new block();

$block1->headingForm($strings["team_task_details"]);

echo '<dl class="row">';
if ($taskDetail->tas_name[0] != "") {
    echo '<dt class="col-sm-3">' . htmlspecialchars($strings["name"]) . ':</dt>
<dd class="col-sm-9">' . htmlspecialchars($taskDetail->tas_name[0]) . '</dd>';
} 
if ($taskDetail->tas_description[0] != "") {
    echo '<dt class="col-sm-3">' . htmlspecialchars($strings["description"]) . ':</dt>
<dd class="col-sm-9">' . nl2br(htmlspecialchars($taskDetail->tas_description[0])) . '</dd>';
} 
$complValue = ($taskDetail->tas_completion[0] > 0) ? $taskDetail->tas_completion[0] . "0 %": $taskDetail->tas_completion[0] . " %";
echo '<dt class="col-sm-3">' . htmlspecialchars($strings["completion"]) . ':</dt>
<dd class="col-sm-9">' . htmlspecialchars($complValue) . '</dd>';
if ($taskDetail->tas_assigned_to[0] != "0") {
    echo '<dt class="col-sm-3">' . htmlspecialchars($strings["assigned_to"]) . ':</dt>
<dd class="col-sm-9">' . htmlspecialchars($taskDetail->tas_mem_name[0]) . '</dd>';
} 
if ($taskDetail->tas_comments[0] != "") {
    echo '<dt class="col-sm-3">' . htmlspecialchars($strings["comments"]) . ':</dt>
<dd class="col-sm-9">' . nl2br(htmlspecialchars($taskDetail->tas_comments[0])) . '</dd>';
} 
if ($taskDetail->tas_start_date[0] != "") {
    echo '<dt class="col-sm-3">' . htmlspecialchars($strings["start_date"]) . ':</dt>
<dd class="col-sm-9">' . htmlspecialchars($taskDetail->tas_start_date[0]) . '</dd>';
} 
if ($taskDetail->tas_due_date[0] != "") {
    echo '<dt class="col-sm-3">' . htmlspecialchars($strings["due_date"]) . ':</dt>
<dd class="col-sm-9">' . htmlspecialchars($taskDetail->tas_due_date[0]) . '</dd>';
} 
echo '<dt class="col-sm-3">' . htmlspecialchars($strings["updates_task"]) . ':</dt>
<dd class="col-sm-9">';
$tmpquery = "WHERE upd.type='1' AND upd.item = '$id' ORDER BY upd.created DESC";
$listUpdates = new request();
$listUpdates->openUpdates($tmpquery);
$comptListUpdates = count($listUpdates->upd_id);

if ($comptListUpdates != "0") {
    $j = 1;
    for ($i = 0;$i < $comptListUpdates;$i++) {
        echo '<strong>' . $j . '.</strong> <em>' . htmlspecialchars(createDate($listUpdates->upd_created[$i], $_SESSION['timezoneSession'])) . '</em><br>' . nl2br(htmlspecialchars($listUpdates->upd_comments[$i]));
        echo "<br>";
        $j++;
    } 
} else {
    echo htmlspecialchars($strings["no_items"]);
} 

echo '</dd>
</dl>
<hr>';

echo "<br><br>
<a href=\"showallteamtasks.php\">" . $strings["show_all"] . "</a>";

$block1->headingForm_close();
require_once ("include_footer.php");

?>
