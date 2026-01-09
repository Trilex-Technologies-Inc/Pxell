<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: showallteamtasks.php,v 1.5 2005/01/04 06:40:43 luiswang Exp $
 * 
 * Copyright (c) 2003 by the NetOffice developers
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$projectSite = "true";

$checkSession = true;
require_once("../includes/library.php");

$bouton[2] = "over";
$titlePage = $strings["team_tasks"];
require_once ("include_header.php");

$tmpquery = "WHERE tas.project = '" . $_SESSION['projectSession'] . "' AND tas.assigned_to != '0' AND tas.published = '0' AND tas.milestone <> '0' AND mem.organization = '1' ORDER BY tas.name";
$listTasks = new request();
$listTasks->openTasks($tmpquery);
$comptListTasks = count($listTasks->tas_id);

$block1 = new block();

$block1->headingForm($strings["team_tasks"]);

if ($comptListTasks != "0") {
    if ($activeJpgraph == "true") {
        // show the expanded or compact Gantt Chart
        if ($_GET['base'] == 1) {
            echo "<a href='showallteamtasks.php'>expand</a><br>";
        } else {
            echo "<a href='showallteamtasks.php?base=1'>compact</a><br>";
        }

        echo "<img src=\"graphtasks.php?project=" . $projectDetail->pro_id[0] . '&amp;base=' . $_GET['base'] . "\" alt=\"\"><br>
<span class=\"listEvenBold\">Powered by <a href=\"http://www.aditus.nu/jpgraph/\" target=\"_blank\">JpGraph</a></span><br><br>";
    }

    echo '<div class="table-responsive">
<table class="table table-striped table-hover">
<thead class="table-dark">
<tr>
<th>' . htmlspecialchars($strings["name"]) . '</th>
<th>' . htmlspecialchars($strings["description"]) . '</th>
<th>' . htmlspecialchars($strings["status"]) . '</th>
<th>' . htmlspecialchars($strings["due"]) . '</th>
</tr>
</thead>
<tbody>';

    for ($i = 0;$i < $comptListTasks;$i++) {
        if ($listTasks->tas_due_date[$i] == "") {
            $listTasks->tas_due_date[$i] = $strings["none"];
        }
        $idStatus = $listTasks->tas_status[$i];
        echo '<tr>
<td><a href="teamtaskdetail.php?id=' . $listTasks->tas_id[$i] . '">' . htmlspecialchars($listTasks->tas_name[$i]) . '</a></td>
<td>' . nl2br(htmlspecialchars($listTasks->tas_description[$i])) . '</td>
<td>' . htmlspecialchars($status[$idStatus]) . '</td>
<td>' . htmlspecialchars($listTasks->tas_due_date[$i]) . '</td>
</tr>';
    }

    echo '</tbody>
</table>
</div>';
}
else {
    echo '<div class="alert alert-info">' . htmlspecialchars($strings["no_items"]) . '</div>';
}

echo "<br><br>

<a href=\"addteamtask.php\" class=\"FooterCell\">" . $strings["add_task"] . "</a>";

$block1->headingForm_close();
require_once ("include_footer.php");

?>
