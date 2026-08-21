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
?>
<style>
    .report-builder {
        display: grid;
        gap: 22px;
    }

    .report-hero {
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

    .report-hero__eyebrow {
        color: #2f6f6a;
        font-size: 0.78rem;
        font-weight: 750;
        letter-spacing: 0.08em;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .report-hero h1 {
        color: #162033;
        font-size: 1.9rem;
        font-weight: 750;
        letter-spacing: 0;
        margin: 0 0 8px;
    }

    .report-hero p {
        color: #657487;
        margin: 0;
        max-width: 58rem;
    }

    .report-hero__badge {
        width: 72px;
        height: 72px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #e8f0f7;
        color: #164773;
        font-size: 1.8rem;
    }

    .report-tabs {
        display: inline-flex;
        gap: 6px;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(34, 49, 72, 0.06);
        padding: 6px;
        width: fit-content;
    }

    .report-tab {
        border-radius: 6px;
        color: #526174;
        font-weight: 750;
        padding: 9px 12px;
        text-decoration: none;
    }

    .report-tab:hover {
        color: #164773;
        text-decoration: none;
    }

    .report-tab--active {
        background: #164773;
        color: #ffffff;
    }

    .report-tab--active:hover {
        color: #ffffff;
    }

    .report-card {
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 12px 36px rgba(34, 49, 72, 0.09);
        padding: 20px;
    }

    .report-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
    }

    .report-field {
        display: grid;
        gap: 10px;
        min-width: 0;
    }

    .report-field--wide {
        grid-column: 1 / -1;
    }

    .report-field__label {
        color: #334155;
        font-weight: 750;
        margin: 0;
    }

    .report-field select[multiple] {
        min-height: 132px;
    }

    .report-date-panel {
        background: #f8fbfd;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        display: grid;
        gap: 12px;
        padding: 14px;
    }

    .report-date-panel__options {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .report-date-panel__range {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 10px;
        align-items: center;
    }

    .report-submit {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .report-submit .btn {
        min-height: 44px;
        padding-left: 22px;
        padding-right: 22px;
    }

    .custom-report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 14px;
    }

    .custom-report {
        display: grid;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 10px 28px rgba(34, 49, 72, 0.07);
        color: #162033;
        min-height: 160px;
        padding: 18px;
        text-decoration: none;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    }

    .custom-report:hover {
        border-color: #b7c6d5;
        box-shadow: 0 16px 34px rgba(34, 49, 72, 0.12);
        color: #162033;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .custom-report__icon {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: #e8f4f4;
        color: #2f6f6a;
    }

    .custom-report__title {
        font-size: 1rem;
        font-weight: 750;
    }

    .custom-report__desc {
        color: #657487;
        font-size: 0.9rem;
        line-height: 1.45;
    }

    @media (max-width: 720px) {
        .report-hero {
            grid-template-columns: 1fr;
            padding: 20px;
        }

        .report-hero__badge {
            width: 56px;
            height: 56px;
            font-size: 1.35rem;
        }

        .report-tabs {
            display: grid;
            width: 100%;
        }

        .report-date-panel__range {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="report-builder">
    <section class="report-hero">
        <div>
            <div class="report-hero__eyebrow"><?php echo $strings['reports']; ?></div>
            <h1><?php echo ($typeReports == 'create') ? $strings['create_report'] : $strings['custom_reports']; ?></h1>
            <p><?php echo ($typeReports == 'create') ? $strings['report_intro'] : $strings['custom_report_intro']; ?></p>
        </div>
        <div class="report-hero__badge" aria-hidden="true">
            <i class="fa fa-chart-line"></i>
        </div>
    </section>

    <nav class="report-tabs" aria-label="Report modes">
        <a class="report-tab <?php echo ($typeReports == 'create') ? 'report-tab--active' : ''; ?>" href="../reports/createreport.php?typeReports=create"><?php echo $strings['create_report']; ?></a>
        <a class="report-tab <?php echo ($typeReports == 'custom') ? 'report-tab--active' : ''; ?>" href="../reports/createreport.php?typeReports=custom"><?php echo $strings['custom_reports']; ?></a>
    </nav>

<?php
if ($typeReports == 'create') {
    echo '<form method="POST" action="../reports/resultsreport.php" name="customsearchForm" class="report-card">';
    echo '<div class="report-filter-grid">';

    echo '<div class="mb-3">';
    echo '<label class="report-field__label">' . $strings["clients"] . '</label>';

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
    echo '<label class="report-field__label">' . $strings["projects"] . '</label>';

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
    echo '<label class="report-field__label">' . $strings["assigned_to"] . '</label>';

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
    echo '<div class="report-field report-field--wide">';
    echo '<label class="report-field__label">' . $strings["due_date"] . '</label>';
    echo '<div class="report-date-panel">';

    echo '<div class="report-date-panel__options">';
    echo '<div class="form-check">';
    echo '<input checked class="form-check-input" type="radio" name="S_DUEDATE" value="ALL" id="due_all">';
    echo '<label for="due_all" class="form-check-label">' . $strings["all_dates"] . '</label>';
    echo '</div>';

    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="radio" name="S_DUEDATE" value="DATERANGE" id="due_range">';
    echo '<label for="due_range" class="form-check-label">' . $strings["between_dates"] . '</label>';
    echo '</div>';
    echo '</div>';

    echo '<div class="report-date-panel__range">';
    echo '<input type="date" name="S_SDATE" id="sel1" class="form-control" placeholder="Start date">';
    echo '<span>' . $strings["and"] . '</span>';
    echo '<input type="date" name="S_EDATE" id="sel3" class="form-control" placeholder="End date">';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    /* -------- Complete Date -------- */
    echo '<div class="report-field report-field--wide">';
    echo '<label class="report-field__label">' . $strings["complete_date"] . '</label>';
    echo '<div class="report-date-panel">';

    echo '<div class="report-date-panel__options">';
    echo '<div class="form-check">';
    echo '<input checked class="form-check-input" type="radio" name="S_COMPLETEDATE" value="ALL" id="complete_all">';
    echo '<label for="complete_all" class="form-check-label">' . $strings["all_dates"] . '</label>';
    echo '</div>';

    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="radio" name="S_COMPLETEDATE" value="DATERANGE" id="complete_range">';
    echo '<label for="complete_range" class="form-check-label">' . $strings["between_dates"] . '</label>';
    echo '</div>';
    echo '</div>';

    echo '<div class="report-date-panel__range">';
    echo '<input type="date" name="S_SDATE2" id="sel5" class="form-control" placeholder="Start date">';
    echo '<span>' . $strings["and"] . '</span>';
    echo '<input type="date" name="S_EDATE2" id="sel7" class="form-control" placeholder="End date">';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    /* -------- Status -------- */
    echo '<div class="mb-3">';
    echo '<label class="report-field__label">' . $strings["status"] . '</label>';
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
    echo '<label class="report-field__label">' . $strings["priority"] . '</label>';
    echo '<select name="S_PRIOSEL[]" size="4" multiple class="form-select">';
    echo '<option value="ALL" selected>' . $strings["select_all"] . '</option>';
    $comptPri = count($priority);
    for ($i = 0; $i < $comptPri; $i++) {
        echo '<option value="' . $i . '">' . $priority[$i] . '</option>';
    }
    echo '</select>';
    echo '</div>';

    /* -------- Submit Button -------- */
    echo '</div>';
    echo '<div class="report-submit">';
    echo '<input type="submit" name="Save" value="' . $strings["create"] . '" class="btn btn-primary">';
    echo '</div>';
    echo '</form>';
}
else if ($typeReports == 'custom') {
    $customReports = array(
        array('../reports/selectcompleted.php?typeReports=' . $typeReports, $strings['completed_task_report'], $strings['completed_task_report_desc'], 'fa-check-double'),
        array('../reports/selecthours.php?typeReports=' . $typeReports, $strings['time_report'], $strings['time_report_desc'], 'fa-clock'),
        array('../reports/overdue.php?typeReports=' . $typeReports, $strings['overdue_tasks'], $strings['overdue_tasks_desc'], 'fa-triangle-exclamation'),
        array('../reports/snapshot.php?typeReports=' . $typeReports, $strings['project_snapshot'], $strings['project_snapshot_desc'], 'fa-camera-retro'),
        array('../reports/phasestatus.php?typeReports=' . $typeReports, $strings['project_phasestatus'], $strings['project_phasestatus_desc'], 'fa-chart-simple'),
        array('../reports/selectru.php?typeReports=' . $typeReports, $strings['resource_usage'], $strings['resource_usage_desc'], 'fa-users-gear'),
        array('../reports/projectbreakdown.php?typeReports=' . $typeReports, $strings['project_breakdown'], $strings['project_breakdown_desc'], 'fa-diagram-project')
    );

    echo '<section class="custom-report-grid">';
    foreach ($customReports as $report) {
        echo '<a class="custom-report" href="' . htmlspecialchars($report[0]) . '">';
        echo '<span class="custom-report__icon"><i class="fa ' . htmlspecialchars($report[3]) . '"></i></span>';
        echo '<span class="custom-report__title">' . htmlspecialchars($report[1]) . '</span>';
        echo '<span class="custom-report__desc">' . htmlspecialchars($report[2]) . '</span>';
        echo '</a>';
    }
    echo '</section>';
}
?>
</div>
<?php

require_once('../themes/' . THEME . '/footer.php');

?>
