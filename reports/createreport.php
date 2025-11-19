<?php // $Revision: 1.8 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: createreport.php,v 1.8 2004/12/23 16:39:19 pixtur Exp $
 * 
 * Copyright (c) 2003 by the NetOffice developers
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$checkSession = true;
require_once('../includes/library.php');

if ($typeReports == '') {
    $typeReports = 'create';
}

//--- header ----
$breadcrumbs[]=$strings['reports'];
if ($typeReports == 'create') {
    $breadcrumbs[]=$strings['create_report'] . ' | ' . buildLink('../reports/createreport.php?typeReports=custom', $strings['custom_reports'], LINK_INSIDE);
} 
else if ($typeReports == 'custom') {
    $breadcrumbs[]=buildLink('../reports/createreport.php?typeReports=create', $strings["create_report"], LINK_INSIDE) . ' | ' . $strings['custom_reports'];
} 

$pageSection = 'reports';
require_once('../themes/' . THEME . '/header.php');

//---- content ------
if ($typeReports == 'create') {
    $block1 = new block();

    $block1->form = "customsearch";
    $block1->openForm("../reports/resultsreport.php");

    $block1->headingForm($strings["create_report"]);

    $block1->openContent();
    $block1->contentTitle($strings["report_intro"]);

    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["clients"] . ' :</label>';

    if ($clientsFilter == "true" && $_SESSION['profilSession'] == "2") {
        $teamMember = "false";
        $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "'";
        $memberTest = new request();
        $memberTest->openTeams($tmpquery);
        $comptMemberTest = count($memberTest->tea_id);

        if ($comptMemberTest == "0") {
            $listClients = "false";
        } else {
            for ($i = 0; $i < $comptMemberTest; $i++) {
                $clientsOk .= $memberTest->tea_org2_id[$i];
                if ($comptMemberTest - 1 != $i) {
                    $clientsOk .= ",";
                }
            }

            if ($clientsOk == "") {
                $listClients = "false";
            } else {
                $tmpquery = "WHERE org.id IN($clientsOk) AND org.id != '1' ORDER BY org.name";
            }
        }
    } else if ($clientsFilter == "true" && $_SESSION['profilSession'] == "1") {
        $tmpquery = "WHERE org.owner = '" . $_SESSION['idSession'] . "' AND org.id != '1' ORDER BY org.name";
    } else {
        $tmpquery = "WHERE org.id != '1' ORDER BY org.name";
    }

    $listOrganizations = new request();
    $listOrganizations->openOrganizations($tmpquery);
    $comptListOrganizations = count($listOrganizations->org_id);

    echo '<select name="S_ORGSEL[]" size="4" multiple class="form-select">';
    echo '<option selected value="ALL">' . $strings["select_all"] . '</option>';
    for ($i = 0; $i < $comptListOrganizations; $i++) {
        echo '<option value="' . $listOrganizations->org_id[$i] . '">' . $listOrganizations->org_name[$i] . '</option>';
    }
    echo '</select>';
    echo '</div>';

    /* -------- Projects -------- */
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["projects"] . ' :</label>';

    if ($projectsFilter == "true") {
        $tmpquery = "LEFT OUTER JOIN " . $tableCollab["teams"] . " teams ON teams.project = pro.id ";
        $tmpquery .= "WHERE pro.status IN(0,2,3) AND teams.member = '" . $_SESSION['idSession'] . "' ORDER BY pro.name";
    } else {
        $tmpquery = "WHERE pro.status IN(0,2,3) ORDER BY pro.name";
    }

    $listProjects = new request();
    $listProjects->openProjects($tmpquery);
    $comptListProjects = count($listProjects->pro_id);

    echo '<select name="S_PRJSEL[]" size="4" multiple class="form-select">';
    echo '<option selected value="ALL">' . $strings["select_all"] . '</option>';
    for ($i = 0; $i < $comptListProjects; $i++) {
        echo '<option value="' . $listProjects->pro_id[$i] . '">' . $listProjects->pro_name[$i] . '</option>';
    }
    echo '</select>';
    echo '</div>';

    /* -------- Assigned To -------- */
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["assigned_to"] . ' :</label>';

    if ($demoMode == true) {
        $tmpquery = "ORDER BY mem.name";
    } else {
        $tmpquery = "WHERE mem.id != '2' ORDER BY mem.name";
    }

    $listMembers = new request();
    $listMembers->openMembers($tmpquery);
    $comptListMembers = count($listMembers->mem_id);

    echo '<select name="S_ATSEL[]" size="4" multiple class="form-select">';
    echo '<option selected value="ALL">' . $strings["select_all"] . '</option>';
    echo '<option value="0">' . $strings["unassigned"] . '</option>';
    for ($i = 0; $i < $comptListMembers; $i++) {
        echo '<option value="' . $listMembers->mem_id[$i] . '">' . $listMembers->mem_login[$i];
        if ($listMembers->mem_profil[$i] == "3") {
            echo ' (' . $strings["client_user"] . ')';
        }
        echo '</option>';
    }
    echo '</select>';
    echo '</div>';

    /* -------- Due Date -------- */
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["due_date"] . ' :</label>';

    echo '<div class="form-check">';
    echo '<input checked class="form-check-input" type="radio" name="S_DUEDATE" value="ALL" id="due_all">';
    echo '<label for="due_all" class="form-check-label">' . $strings["all_dates"] . '</label>';
    echo '</div>';

    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="radio" name="S_DUEDATE" value="DATERANGE" id="due_range">';
    echo '<label for="due_range" class="form-check-label">' . $strings["between_dates"] . '</label>';
    echo '</div>';

    echo '<div class="d-flex align-items-center gap-2 mt-2">';
    echo '<input type="date" name="S_SDATE" id="sel1" class="form-control" placeholder="Start date">';
    echo '<button type="reset" id="trigger_a" class="btn btn-outline-secondary">...</button>';
    echo '</div>';

    echo '<div class="d-flex align-items-center gap-2 mt-2">';
    echo '<span>' . $strings["and"] . '</span>';
    echo '<input type="date" name="S_EDATE" id="sel3" class="form-control" placeholder="End date">';
    echo '<button type="reset" id="trigger_b" class="btn btn-outline-secondary">...</button>';
    echo '</div>';
    echo '</div>';

    /* -------- Complete Date -------- */
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["complete_date"] . ' :</label>';

    echo '<div class="form-check">';
    echo '<input checked class="form-check-input" type="radio" name="S_COMPLETEDATE" value="ALL" id="complete_all">';
    echo '<label for="complete_all" class="form-check-label">' . $strings["all_dates"] . '</label>';
    echo '</div>';

    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="radio" name="S_COMPLETEDATE" value="DATERANGE" id="complete_range">';
    echo '<label for="complete_range" class="form-check-label">' . $strings["between_dates"] . '</label>';
    echo '</div>';

    echo '<div class="d-flex align-items-center gap-2 mt-2">';
    echo '<input type="date" name="S_SDATE2" id="sel5" class="form-control" placeholder="Start date">';
    echo '<button type="reset" id="trigger_c" class="btn btn-outline-secondary">...</button>';
    echo '</div>';

    echo '<div class="d-flex align-items-center gap-2 mt-2">';
    echo '<span>' . $strings["and"] . '</span>';
    echo '<input type="date" name="S_EDATE2" id="sel7" class="form-control" placeholder="End date">';
    echo '<button type="reset" id="trigger_d" class="btn btn-outline-secondary">...</button>';
    echo '</div>';
    echo '</div>';

    /* -------- Status -------- */
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["status"] . ' :</label>';
    echo '<select name="S_STATSEL[]" size="4" multiple class="form-select">';
    echo '<option value="ALL" selected>' . $strings["select_all"] . '</option>';
    $comptSta = count($status);
    for ($i = 0; $i < $comptSta; $i++) {
        echo '<option value="' . $i . '">' . $status[$i] . '</option>';
    }
    echo '</select>';
    echo '</div>';

    /* -------- Priority -------- */
    echo '<div class="mb-3">';
    echo '<label class="form-label">' . $strings["priority"] . ' :</label>';
    echo '<select name="S_PRIOSEL[]" size="4" multiple class="form-select">';
    echo '<option value="ALL" selected>' . $strings["select_all"] . '</option>';
    $comptPri = count($priority);
    for ($i = 0; $i < $comptPri; $i++) {
        echo '<option value="' . $i . '">' . $priority[$i] . '</option>';
    }
    echo '</select>';
    echo '</div>';

    /* -------- Submit Button -------- */
    echo '<div class="mt-3">';
    echo '<input type="submit" name="Save" value="' . $strings["create"] . '" class="btn btn-primary">';
    echo '</div>';

    $block1->closeContent();

	$block1->headingForm_close();

    $block1->closeForm();
}
else if ($typeReports == 'custom') {
    $block1 = new block();
    $block1->headingForm($strings['custom_reports']);

    $block1->openContent();
    $block1->contentTitle($strings['custom_report_intro']);

    $block1->contentRow(buildLink('../reports/selectcompleted.php?typeReports=' . $typeReports, $strings['completed_task_report'], LINK_INSIDE), $strings['completed_task_report_desc'], true);

    $block1->contentRow(buildLink('../reports/selecthours.php?typeReports=' . $typeReports, $strings['time_report'], LINK_INSIDE), $strings['time_report_desc'], true);

    $block1->contentRow(buildLink('../reports/overdue.php?typeReports=' . $typeReports, $strings['overdue_tasks'], LINK_INSIDE), $strings['overdue_tasks_desc'], true);

    $block1->contentRow(buildLink('../reports/snapshot.php?typeReports=' . $typeReports, $strings['project_snapshot'], LINK_INSIDE), $strings['project_snapshot_desc'], true);

    $block1->contentRow(buildLink('../reports/phasestatus.php?typeReports=' . $typeReports, $strings['project_phasestatus'], LINK_INSIDE), $strings['project_phasestatus_desc'], true);

//    $block1->contentRow(buildLink('../custom/pending_tasks.php?typeReports=' . $typeReports, $strings['pending_tasks'], LINK_INSIDE), $strings['pending_tasks_desc'], true);
                                                                                             
//    $block1->contentRow(buildLink('../reports/pm_report.php?typeReports=' . $typeReports, $strings['pm_report'], LINK_INSIDE), $strings['pm_report_desc'], true);

    $block1->contentRow(buildLink('../reports/selectru.php?typeReports=' . $typeReports, $strings['resource_usage'], LINK_INSIDE), $strings['resource_usage_desc'], true);

    $block1->contentRow(buildLink('../reports/projectbreakdown.php?typeReports=' . $typeReports, $strings['project_breakdown'], LINK_INSIDE), $strings['project_breakdown_desc'], true);

    $block1->closeContent();
    $block1->headingForm_close();
}

require_once('../themes/' . THEME . '/footer.php');

?>