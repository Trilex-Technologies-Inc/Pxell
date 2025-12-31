<?php // $Revision: 1.10 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: edittask.php,v 1.10 2005/05/23 22:34:58 madbear Exp $
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

// case multiple edit tasks
$multi = strstr($id, '**');

if ($multi != '') {
    header("Location: ../tasks/updatetasks.php?report=$report&project=$project&id=$id");
    exit;
}

if ($id != '' && $action != 'update' && $action != 'add') {
    $tmpquery = "WHERE tas.id = '$id'";
    $taskDetail = new request();
    $taskDetail->openTasks($tmpquery);
    $tmpquery = "WHERE pro.id = '" . $taskDetail->tas_project[0] . "'";
    $project = $taskDetail->tas_project[0];
} else {
    $tmpquery = "WHERE pro.id = '$project'";
}

$projectDetail = new request();
$projectDetail->openProjects($tmpquery);

$teamMember = 'false';
$tmpquery = "WHERE tea.project = '$project' AND tea.member = '" . $_SESSION['idSession'] . "'";
$memberTest = new request();
$memberTest->openTeams($tmpquery);
$comptMemberTest = count($memberTest->tea_id);

if ($comptMemberTest == '0') {
    $teamMember = 'false';
} else {
    $teamMember = 'true';
}

if ($teamMember == 'false' && $_SESSION['profilSession'] != '5') {
    header("Location: ../tasks/listtasks.php?project=$project&msg=taskOwner");
    exit;
}

// case update or copy task
if ($id != '') {
    // case update or copy task
    if ($action == 'update') {
        // concat values from date selector and replace quotes by html code in name
        $tn = convertData($tn);
        $d = convertData($d);
        $c = convertData($c);

        // case copy task
        if ($cpy == 'true') {
            // Change task status if parent phase is suspended, complete or not open.
            if ($projectDetail->pro_phase_set[0] != '0') {
                $tmpquery = "WHERE pha.project_id = '$project' AND pha.order_num = '$pha'";
                $currentPhase = new request();
                $currentPhase->openPhases($tmpquery);
                if ($st == 3 && $currentPhase->pha_status[0] != 1) {
                    $st = 4;
                }
            }

            if ($compl == '10' && $st != '0') {
                $st = '1';
            }

            if ($pub == '') {
                $pub = '1';
            }

            if ($miles == '') {
                $miles = '1';
            }

            if ($miles == '0') {
                $at = '0';
                $st = '2';
                $pr = '0';
                $dd = $sd;
                $etm = '0';
                $compl = '0';
                $serv = '0';
                $pub = '1';
            }

            // Insert Task details with or without parent phase
            if ($projectDetail->pro_phase_set[0] != '0') {
                $tmpquery1 = 'INSERT INTO ' . $tableCollab['tasks'] . "(project,name,description,owner,assigned_to,status,priority,start_date,due_date,estimated_time,comments,created,published,completion,parent_phase,service,milestone) VALUES('$project','$tn','$d','" . $_SESSION['idSession'] . "','$at','$st','$pr','$sd','$dd','$etm','$c','$dateheure','$pub','$compl','$pha','$serv','$miles')";
            } else {
                $tmpquery1 = 'INSERT INTO ' . $tableCollab['tasks'] . "(project,name,description,owner,assigned_to,status,priority,start_date,due_date,estimated_time,comments,created,published,completion,service,milestone) VALUES('$project','$tn','$d','" . $_SESSION['idSession'] . "','$at','$st','$pr','$sd','$dd','$etm','$c','$dateheure','$pub','$compl','$serv','$miles')";
            }

            connectSql($tmpquery1);
            $tmpquery = $tableCollab['tasks'];
            last_id($tmpquery);
            $num = $lastId[0];
            unset($lastId);

            if ($st == '1' && $cd != '--') {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET complete_date='$date' WHERE id = '$num'";
                connectSql($tmpquery6);
            }

            // if assigned_to not blank, set assigned date
            if ($at != '0') {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET assigned='$dateheure' WHERE id = '$num'";
                connectSql($tmpquery6);
            }

            $tmpquery2 = 'INSERT INTO ' . $tableCollab['assignments'] . "(task,owner,assigned_to,assigned) VALUES('$num','" . $_SESSION['idSession'] . "','$at','$dateheure')";
            connectSql($tmpquery2);

            // if assigned_to not blank, add to team members (only if doesn't already exist)
            if ($at != '0') {
                $tmpquery = "WHERE tea.project = '$project' AND tea.member = '$at'";
                $testinTeam = new request();
                $testinTeam->openTeams($tmpquery);
                $comptTestinTeam = count($testinTeam->tea_id);

                if ($comptTestinTeam == '0') {
                    $tmpquery3 = 'INSERT INTO ' . $tableCollab['teams'] . "(project,member,published,authorized) VALUES('$project','$at','1','0')";
                    connectSql($tmpquery3);
                }

                // send task assignment mail if notifications = true
                if ($notifications == 'true') {
                    include_once('../tasks/noti_taskassignment.php');
                }
            }

            // create task sub-folder if filemanagement = true
            if ($fileManagement == 'true') {
                createDir("files/$project/$num");
            }

            header("Location: ../tasks/viewtask.php?id=$num&msg=addAssignment");
            exit;
        } else {
            // case update task
            // Change task status if parent phase is suspended, complete or not open.
            if ($projectDetail->pro_phase_set[0] != '0') {
                $tmpquery = "WHERE pha.project_id = '$project' AND pha.order_num = '$pha'";
                $currentPhase = new request();
                $currentPhase->openPhases($tmpquery);

                if ($st == 3 && $currentPhase->pha_status[0] != 1) {
                    $st = 4;
                }
            }

            if ($pub == '') {
                $pub = '1';
            }

            if ($miles == '') {
                $miles = '1';
            }

            // set task as completed if comlpetion is 100%, unless client completed
            if ($compl == '10' && $st != '0') {
                $st = '1';
            }

            if ($miles == '0') {
                $at = '0';
                $st = '2';
                $pr = '0';
                $dd = $sd;
                $etm = '0';
                $compl = '0';
                $serv = '0';
                $pub = '1';
            }

            // Update task with our without parent phase
            if ($projectDetail->pro_phase_set[0] != '0') {
                $tmpquery5 = 'UPDATE ' . $tableCollab['tasks'] . " SET name='$tn',description='$d',assigned_to='$at',status='$st',priority='$pr',start_date='$sd',due_date='$dd',estimated_time='$etm',comments='$c',modified='$dateheure',completion='$compl',parent_phase='$pha',published='$pub', service='$serv', milestone='$miles' WHERE id = '$id'";
            } else {
                $tmpquery5 = 'UPDATE ' . $tableCollab['tasks'] . " SET name='$tn',description='$d',assigned_to='$at',status='$st',priority='$pr',start_date='$sd',due_date='$dd',estimated_time='$etm',comments='$c',modified='$dateheure',completion='$compl',published='$pub', service='$serv', milestone='$miles' WHERE id = '$id'";
            }

            if ($st == '1' && $cd == '--') {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET complete_date='$date' WHERE id = '$id'";
                connectSql($tmpquery6);
            } else {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET complete_date='$cd' WHERE id = '$id'";
                connectSql($tmpquery6);
            }

            if ($old_st == '1' && $st != $old_st) {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET complete_date='' WHERE id = '$id'";
                connectSql($tmpquery6);
            }

            // if project different from past value, set project number in tasks table
            if ($project != $old_project) {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET project='$project' WHERE id = '$id'";
                connectSql($tmpquery6);

                $tmpquery7 = 'UPDATE ' . $tableCollab['files'] . " SET project='$project' WHERE task = '$id'";
                connectSql($tmpquery7);

                $tmpquery8 = 'UPDATE ' . $tableCollab['tasks_time'] . " SET project='$project' WHERE task = '$id'";
                connectSql($tmpquery8);

                createDir("files/$project/$id");
                $dir = opendir("../files/$old_project/$id");

                if (is_resource($dir)) {
                    while ($v = readdir($dir)) {
                        if ($v != '.' && $v != '..') {
                            copy("../files/$old_project/$id/" . $v, "../files/$project/$id/" . $v);
                            @unlink("../files/$old_project/$id/" . $v);
                        }
                    }
                }
            }

            // if assigned_to not blank and past assigned value blank, set assigned date
            if ($at != '0' && $old_assigned == '') {
                $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET assigned='$dateheure' WHERE id = '$id'";
                connectSql($tmpquery6);
            }

            // if assigned_to different from past value, insert into assignment
            // add new assigned_to in team members (only if doesn't already exist)
            if ($at != $old_at) {
                $tmpquery2 = "INSERT INTO " . $tableCollab["assignments"] . "(task,owner,assigned_to,assigned) VALUES('$id','" . $_SESSION['idSession'] . "','$at','$dateheure')";
                connectSql("$tmpquery2");
                $tmpquery = "WHERE tea.project = '$project' AND tea.member = '$at'";
                $testinTeam = new request();
                $testinTeam->openTeams($tmpquery);
                $comptTestinTeam = count($testinTeam->tea_id);

                if ($comptTestinTeam == "0") {
                    $tmpquery3 = "INSERT INTO " . $tableCollab["teams"] . "(project,member,published,authorized) VALUES('$project','$at','1','0')";
                    connectSql("$tmpquery3");
                }

                $msg = "updateAssignment";
                connectSql("$tmpquery5");
                $tmpquery = "WHERE tas.id = '$id'";
                $taskDetail = new request();
                $taskDetail->openTasks($tmpquery);

                // send task assignment mail if notifications = true
                if ($notifications == "true") {
                    require_once("../tasks/noti_taskassignment.php");
                }
            } else {
                $msg = "update";
                connectSql("$tmpquery5");
                $tmpquery = "WHERE tas.id = '$id'";
                $taskDetail = new request();
                $taskDetail->openTasks($tmpquery);

                // send status task change mail if notifications = true
                if ($at != "0" && $st != $old_st) {
                    if ($notifications == "true") {
                        require_once("../tasks/noti_statustaskchange.php");
                    }
                }

                // send priority task change mail if notifications = true
                if ($at != "0" && $pr != $old_pr) {
                    if ($notifications == "true") {
                        require_once("../tasks/noti_prioritytaskchange.php");
                    }
                }

                // send due date task change mail if notifications = true
                if ($at != "0" && $dd != $old_dd) {
                    if ($notifications == "true") {
                        require_once("../tasks/noti_duedatetaskchange.php");
                    }
                }
            }

            if ($st != $old_st) {
                $cUp .= "\n[status:$st]";
            }

            if ($pr != $old_pr) {
                $cUp .= "\n[priority:$pr]";
            }

            if ($dd != $old_dd) {
                $cUp .= "\n[datedue:$dd]";
            }

            if ($cUp != "" || $st != $old_st || $pr != $old_pr || $dd != $old_dd) {
                $cUp = convertData($cUp);
                $tmpquery6 = "INSERT INTO " . $tableCollab["updates"] . "(type,item,member,comments,created) VALUES ('1','$id','" .$_SESSION['idSession'] . "','$cUp','$dateheure')";
                connectSql($tmpquery6);
            }

            header("Location: ../tasks/viewtask.php?id=$id&msg=$msg");
            exit;
        }
    }

    $projActualTime = new request();
    $atm = $projActualTime->getProjectTime($project);
    // set value in form
    $tn = $taskDetail->tas_name[0];
    $d = $taskDetail->tas_description[0];
    $sd = $taskDetail->tas_start_date[0];
    $dd = $taskDetail->tas_due_date[0];
    $cd = $taskDetail->tas_complete_date[0];
    $etm = $taskDetail->tas_estimated_time[0];
    // $atm = $taskDetail->tas_actual_time[0];
    $c = $taskDetail->tas_comments[0];
    $pub = $taskDetail->tas_published[0];
    $miles = $taskDetail->tas_milestone[0];

    if ($pub == "0") {
        $checkedPub = "checked";
    }

    if ($miles == "0") {
        $checkedMileS = "checked";
        $ddDisabled = "disabled";
        $triggerBDisabled = "disabled";
        $cdDisabled = "disabled";
        $triggerCDisabled = "disabled";
        $stDisabled = "disabled";
        $complDisabled = "disabled";
        $prDisabled = "disabled";
        $servDisabled = "disabled";
        $pubDisabled = "disabled";
    }
}
// case add task
if ($id == "") {
    // case add task
    if ($action == "add") {
        // concat values from date selector and replace quotes by html code in name
        $tn = convertData($tn);
        $d = convertData($d);
        $c = convertData($c);

        // Change task status if parent phase is suspended, complete or not open.
        if ($projectDetail->pro_enable_phase[0] == "1") {
            $tmpquery = "WHERE pha.project_id = '$project' AND pha.order_num = '$pha'";
            $currentPhase = new request();
            $currentPhase->openPhases($tmpquery);

            if ($st == 3 && $currentPhase->pha_status[0] != 1) {
                $st = 4;
            }
        }

        if ($compl == '10' && $st != '0') {
            $st = '1';
        }

        if ($pub == '') {
            $pub = '1';
        }

        if ($miles == '') {
            $miles = '1';
        }

        if ($miles == '0') {
            $st = '2';
            $pr = '0';
            $dd = $sd;
            $etm = '0';
            $compl = '0';
            $serv = '0';
            $pub = '1';
        }

        // Insert task with our without parent phase
        if ($projectDetail->pro_phase_set[0] != "0") {
            $tmpquery1 = "INSERT INTO " . $tableCollab["tasks"] . "(project,name,description,owner,assigned_to,status,priority,start_date,due_date,estimated_time,comments,created,published,completion,parent_phase,service,milestone) VALUES('$project','$tn','$d','" . $_SESSION['idSession'] . "','$at','$st','$pr','$sd','$dd','$etm','$c','$dateheure','$pub','$compl','$pha','$serv','$miles')";
        } else {
            $tmpquery1 = "INSERT INTO " . $tableCollab["tasks"] . "(project,name,description,owner,assigned_to,status,priority,start_date,due_date,estimated_time,comments,created,published,completion,service,milestone) VALUES('$project','$tn','$d','" . $_SESSION['idSession'] . "','$at','$st','$pr','$sd','$dd','$etm','$c','$dateheure','$pub','$compl','$serv','$miles')";
        }

        connectSql($tmpquery1);
        $tmpquery = $tableCollab['tasks'];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);

        if ($st == '1') {
            $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET complete_date='$date' WHERE id = '$num'";
            connectSql($tmpquery6);
        }

        // if assigned_to not blank, set assigned date
        if ($at != '0') {
            $tmpquery6 = 'UPDATE ' . $tableCollab['tasks'] . " SET assigned='$dateheure' WHERE id = '$num'";
            connectSql($tmpquery6);
        }

        $tmpquery2 = 'INSERT INTO ' . $tableCollab['assignments'] . "(task,owner,assigned_to,assigned) VALUES('$num','" . $_SESSION['idSession'] . "','$at','$dateheure')";
        connectSql($tmpquery2);

        // if assigned_to not blank, add to team members (only if doesn't already exist)
        // add assigned_to in team members (only if doesn't already exist)
        if ($at != '0') {
            $tmpquery = "WHERE tea.project = '$project' AND tea.member = '$at'";
            $testinTeam = new request();
            $testinTeam->openTeams($tmpquery);
            $comptTestinTeam = count($testinTeam->tea_id);

            if ($comptTestinTeam == "0") {
                $tmpquery3 = "INSERT INTO " . $tableCollab["teams"] . "(project,member,published,authorized) VALUES('$project','$at','1','0')";
                connectSql($tmpquery3);
            }

            // send task assignment mail if notifications = true
            if ($notifications == "true") {
                require_once("../tasks/noti_taskassignment.php");
            }
        }

        // create task sub-folder if filemanagement = true
        if ($fileManagement == "true") {
            createDir("files/$project/$num");
        }

        header("Location: ../tasks/viewtask.php?id=$num&msg=addAssignment");
        exit;
    }

    // set default values
    $taskDetail->tas_assigned_to[0] = $_SESSION['idSession'];
    $taskDetail->tas_priority[0] = $projectDetail->pro_priority[0];
    $taskDetail->tas_status[0] = '3';
}

if ($projectDetail->pro_org_id[0] == '1') {
    $projectDetail->pro_org_name[0] = $strings['none'];
}

if ($projectDetail->pro_phase_set[0] != '0') {
    if ($id != '') {
        $tPhase = $taskDetail->tas_parent_phase[0];

        $tmpquery = "WHERE pha.project_id = '" . $taskDetail->tas_project[0] . "' AND pha.order_num = '$tPhase'";
    }

    if ($id == '') {
        $tPhase = $phase;
        $tmpquery = "WHERE pha.project_id = '$project' AND pha.order_num = '$tPhase'";
    }

    $targetPhase = new request();
    $targetPhase->openPhases($tmpquery);
}

//--- header ---
$breadcrumbs[]=buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../projects/viewproject.php?id=" . $projectDetail->pro_id[0], $projectDetail->pro_name[0], LINK_INSIDE);

if ($projectDetail->pro_phase_set[0] != "0") {
    $breadcrumbs[]=buildLink("../phases/listphases.php?id=" . $projectDetail->pro_id[0], $strings["phases"], LINK_INSIDE);
    $breadcrumbs[]=buildLink("../phases/viewphase.php?id=" . $targetPhase->pha_id[0], $targetPhase->pha_name[0], LINK_INSIDE);
}
$breadcrumbs[]=buildLink("../tasks/listtasks.php?project=" . $projectDetail->pro_id[0], $strings["tasks"], LINK_INSIDE);

if ($id == "") {
    $breadcrumbs[]=$strings["add_task"];
}
else {
    $breadcrumbs[]=buildLink("../tasks/viewtask.php?id=" . $taskDetail->tas_id[0], $taskDetail->tas_name[0], LINK_INSIDE);
    $breadcrumbs[]=$strings["edit_task"];
}


$bodyCommand = "onload=\"document.etDForm.compl.value = document.etDForm.completion.selectedIndex;document.etDForm.tn.focus();\"";
require_once("../themes/" . THEME . "/header.php");

//--- content ----
$block1 = new block();

if ($id == "") {
    $block1->form = "etD";
    $block1->openForm("../tasks/edittask.php?project=$project&amp;action=add#" . $block1->form . "Anchor");
}
if ($id != "") {
    $block1->form = "etD";
    $block1->openForm("../tasks/edittask.php?project=$project&amp;id=$id&amp;action=update&amp;cpy=$cpy#" . $block1->form . "Anchor");
    echo "<input type=\"hidden\" name=\"old_at\" value=\"" . $taskDetail->tas_assigned_to[0] . "\"><input type=\"hidden\" name=\"old_assigned\" value=\"" . $taskDetail->tas_assigned[0] . "\"><input type=\"hidden\" name=\"old_pr\" value=\"" . $taskDetail->tas_priority[0] . "\"><input type=\"hidden\" name=\"old_st\" value=\"" . $taskDetail->tas_status[0] . "\"><input type=\"hidden\" name=\"old_dd\" value=\"" . $taskDetail->tas_due_date[0] . "\"><input type=\"hidden\" name=\"old_project\" value=\"" . $taskDetail->tas_project[0] . "\">";
}

if ($error != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
}

if ($id == "") {
    $block1->headingForm($strings["add_task"]);
}
else {
    if ($cpy == "true") {
        $block1->headingForm($strings["copy_task"] . " : " . $taskDetail->tas_name[0]);
    } else {
        $block1->headingForm($strings["edit_task"] . " : " . $taskDetail->tas_name[0]);
    }
}

$block1->openContent();
?>
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo $strings["info"]; ?></h5>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["project"]; ?> :</label>
                <div class="col-sm-9">
                    <select name="project" class="form-select">
                        <?php
                        if ($projectsFilter == "true") {
                            $tmpquery = "LEFT OUTER JOIN " . $tableCollab["teams"] . " teams ON teams.project = pro.id ";
                            $tmpquery .= "WHERE teams.member = '" . $_SESSION['idSession'] . "'";
                        } else {
                            $tmpquery = "";
                        }
                        $listProjects = new request();
                        $listProjects->openProjects($tmpquery);
                        $comptListProjects = count($listProjects->pro_id);

                        for ($i = 0;$i < $comptListProjects;$i++) {
                            if ($listProjects->pro_id[$i] == $projectDetail->pro_id[0]) {
                                echo "<option value=\"" . $listProjects->pro_id[$i] . "\" selected>" . $listProjects->pro_name[$i] . "</option>";
                            } else {
                                echo "<option value=\"" . $listProjects->pro_id[$i] . "\">" . $listProjects->pro_name[$i] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <?php if ($projectDetail->pro_phase_set[0] != "0"): ?>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["phase"]; ?> :</label>
                    <div class="col-sm-9">
                        <?php echo buildLink("../phases/viewphase.php?id=" . $targetPhase->pha_id[0], $targetPhase->pha_name[0], LINK_INSIDE); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["organization"]; ?> :</label>
                <div class="col-sm-9">
                    <div class="form-control-plaintext"><?php echo $projectDetail->pro_org_name[0]; ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title"><?php echo $strings["details"]; ?></h5>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["name"]; ?> :</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="tn" value="<?php if ($cpy == "true") echo $strings["copy_of"]; echo $tn; ?>" maxlength="100">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["description"]; ?> :</label>
                <div class="col-sm-9">
                    <textarea class="form-control" name="d" rows="4"><?php echo $d; ?></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["assigned_to"]; ?> :</label>
                <div class="col-sm-9">
                    <select name="at" class="form-select">
                        <?php
                        if ($taskDetail->tas_assigned_to[0] == "0") {
                            echo "<option value=\"0\" selected>" . $strings["unassigned"] . "</option>";
                        } else {
                            echo "<option value=\"0\">" . $strings["unassigned"] . "</option>";
                        }

                        $tmpquery = "WHERE tea.project = '$project' ORDER BY mem.name";
                        $assignto = new request();
                        $assignto->openTeams($tmpquery);
                        $comptAssignto = count($assignto->tea_mem_id);

                        for ($i = 0;$i < $comptAssignto;$i++) {
                            $clientUser = "";
                            if ($assignto->tea_mem_profil[$i] == "3") {
                                $clientUser = " (" . $strings["client_user"] . ")";
                            }
                            if ($taskDetail->tas_assigned_to[0] == $assignto->tea_mem_id[$i]) {
                                echo "<option value=\"" . $assignto->tea_mem_id[$i] . "\" selected>" . $assignto->tea_mem_login[$i] . " / " . $assignto->tea_mem_name[$i] . "$clientUser</option>";
                            } else {
                                echo "<option value=\"" . $assignto->tea_mem_id[$i] . "\">" . $assignto->tea_mem_login[$i] . " / " . $assignto->tea_mem_name[$i] . "$clientUser</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["milestone"]; ?> :</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="mileS" value="<?php echo $miles; ?>" onchange="changeMilestone(this)" <?php echo $checkedMileS; ?>>
                        <input type="hidden" name="miles" value="<?php echo $miles; ?>">
                    </div>
                </div>
            </div>

            <?php if ($projectDetail->pro_phase_set[0] != "0"): ?>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["phase"]; ?> :</label>
                    <div class="col-sm-9">
                        <select name="pha" class="form-select">
                            <?php
                            $projectTarget = $projectDetail->pro_id[0];
                            $tmpquery = "WHERE pha.project_id = '$projectTarget' ORDER BY pha.order_num";
                            $projectPhaseList = new request();
                            $projectPhaseList->openPhases($tmpquery);

                            $comptlistPhase = count($projectPhaseList->pha_id);
                            for ($i = 0;$i < $comptlistPhase;$i++) {
                                $phaseNum = $projectPhaseList->pha_order_num[$i];
                                if ($taskDetail->tas_parent_phase[0] == $phaseNum || $phase == $phaseNum) {
                                    echo "<option value=\"$phaseNum\" selected>" . $projectPhaseList->pha_name[$i] . "</option>";
                                } else {
                                    echo "<option value=\"$phaseNum\">" . $projectPhaseList->pha_name[$i] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["status"]; ?> :</label>
                <div class="col-sm-9">
                    <select name="st" class="form-select" onchange="changeSt(this)" <?php echo $stDisabled; ?>>
                        <?php
                        $comptSta = count($status);
                        for ($i = 0;$i < $comptSta;$i++) {
                            if ($taskDetail->tas_status[0] == $i) {
                                echo "<option value=\"$i\" selected>$status[$i]</option>";
                            } else {
                                echo "<option value=\"$i\">$status[$i]</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["completion"]; ?> :</label>
                <div class="col-sm-9">
                    <input type="hidden" name="compl" value="<?php echo $taskDetail->tas_completion[0]; ?>">
                    <select name="completion" class="form-select" onchange="changeCompletion(this)" <?php echo $complDisabled; ?>>
                        <?php
                        for ($i = 0;$i < 11;$i++) {
                            $complValue = ($i > 0) ? $i . "0 %": $i . " %";
                            if ($taskDetail->tas_completion[0] == $i) {
                                echo "<option value=\"" . $i . "\" selected>" . $complValue . "</option>";
                            } else {
                                echo "<option value=\"" . $i . "\">" . $complValue . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["priority"]; ?> :</label>
                <div class="col-sm-9">
                    <select name="pr" class="form-select" <?php echo $prDisabled; ?>>
                        <?php
                        $comptPri = count($priority);
                        for ($i = 0;$i < $comptPri;$i++) {
                            if ($taskDetail->tas_priority[0] == $i) {
                                echo "<option value=\"$i\" selected>$priority[$i]</option>";
                            } else {
                                echo "<option value=\"$i\">$priority[$i]</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["start_date"]; ?> :</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input type="date" class="form-control" name="sd" id="sel1" value="<?php echo $sd; ?>">
                        <button type="button" id="trigger_a" class="btn btn-outline-secondary">...</button>
                    </div>

                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["due_date"]; ?> :</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input type="date" class="form-control" name="dd" id="sel3" value="<?php echo $dd; ?>" <?php echo $ddDisabled; ?>>
                        <button type="button" id="trigger_b" class="btn btn-outline-secondary" <?php echo $triggerBDisabled; ?>>...</button>
                    </div>
                </div>
            </div>

            <?php if ($id != ""): ?>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["complete_date"]; ?> :</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input type="text" class="form-control" name="cd" id="sel5" value="<?php echo $cd; ?>" <?php echo $cdDisabled; ?>>
                            <button type="button" id="trigger_c" class="btn btn-outline-secondary" <?php echo $triggerCDisabled; ?>>...</button>
                        </div>
                        <script type="text/javascript">Calendar.setup({ inputField:"sel5", button:"trigger_c" });</script>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["estimated_time"]; ?> :</label>
                <div class="col-sm-9">
                    <div class="input-group">
                        <input type="text" class="form-control" name="etm" value="<?php echo $etm; ?>">
                        <span class="input-group-text"><?php echo $strings["hours"]; ?></span>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["service"]; ?> :</label>
                <div class="col-sm-9">
                    <select name="serv" class="form-select" <?php echo $servDisabled; ?>>
                        <option value="0">None</option>
                        <?php
                        $tmpquery = 'WHERE 1 ORDER BY  name_print';
                        $serviceDetail = new request();
                        $serviceDetail->openServices($tmpquery);
                        $comptServiceDetail = count($serviceDetail->serv_id);

                        for ($i = 0;$i < $comptServiceDetail;$i++) {
                            $selected = '';
                            if ($serviceDetail->serv_id[$i] == $taskDetail->tas_service[0]) {
                                $selected = 'selected';
                            }
                            echo "<option value='" . $serviceDetail->serv_id[$i] . "' $selected>" . $serviceDetail->serv_name_print[$i] . ' (' . $strings['hourly_rate'] . ' $' . $serviceDetail->serv_hourly_rate[$i] . ')</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?> :</label>
                <div class="col-sm-9">
                    <textarea class="form-control" name="c" rows="4"><?php echo $c; ?></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["published"]; ?> :</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pub" value="0" <?php echo $checkedPub; ?> <?php echo $pubDisabled; ?>>
                    </div>
                </div>
            </div>

            <?php if ($id != ""): ?>
                <h5 class="card-title mt-4"><?php echo $strings["updates_task"]; ?></h5>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?> :</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="cUp" rows="4"></textarea>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-sm-9 offset-sm-3">
                    <button type="submit" class="btn btn-primary"><?php echo $strings["save"]; ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");
?>
<script>
    function changeSt(theObj, firstRun){
        if (theObj.selectedIndex==3) {
            if (firstRun!=true) document.etDForm.completion.selectedIndex=0;
            document.etDForm.compl.value=0;
            document.etDForm.completion.disabled=false;
        } else {
            if (theObj.selectedIndex==0 || theObj.selectedIndex==1) {
                document.etDForm.completion.selectedIndex=10;
                document.etDForm.compl.value=10;
            } else {
                document.etDForm.completion.selectedIndex=0;
                document.etDForm.compl.value=0;
            }
            document.etDForm.completion.disabled=true;
        }
    }

    function changeCompletion(){
        document.etDForm.compl.value = document.etDForm.completion.selectedIndex;
    }

    function changeMilestone(theObj, firstRun){
        if (theObj.checked==true) {
            document.etDForm.miles.value=0;
            document.etDForm.st.disabled=true;
            document.etDForm.completion.disabled=true;
            document.etDForm.pr.disabled=true;
            document.etDForm.serv.disabled=true;
            document.etDForm.dd.disabled=true;
            document.etDForm.trigger_b.disabled=true;
            document.etDForm.etm.disabled=true;
            document.etDForm.cd.disabled=true;
            document.etDForm.trigger_c.disabled=true;
            document.etDForm.pub.disabled=true;
        } else {
            document.etDForm.miles.value=1;
            document.etDForm.st.disabled=false;
            document.etDForm.completion.disabled=false;
            document.etDForm.pr.disabled=false;
            document.etDForm.serv.disabled=false;
            document.etDForm.dd.disabled=false;
            document.etDForm.trigger_b.disabled=false;
            document.etDForm.etm.disabled=false;
            document.etDForm.cd.disabled=false;
            document.etDForm.trigger_c.disabled=false;
            document.etDForm.pub.disabled=false;
        }
    }

    changeSt(document.etDForm.st, true);
    changeMilestone(document.etDForm.mileS, true);
</script>