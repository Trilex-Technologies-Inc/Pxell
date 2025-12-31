<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: addmeetingtime.php,v 1.5 2005/05/27 21:39:26 madbear Exp $
 *
 * Copyright (c) 2004 by the NetOffice developers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

$checkSession = true;
require_once("../includes/library.php");

if ($meeting != "") {
    $cheatCode = "true";
}

if ($meeting != "" && $cheatCode == "true") {
    $id = $meeting;
}

// Meeting Detail
$tmpquery = "WHERE mee.id = '$id'";
$meetingDetail = new request();
$meetingDetail->openMeetings($tmpquery);

// Project Detail
$tmpquery = "WHERE pro.id = '" . $meetingDetail->mee_project[0] . "'";
$projectDetail = new request();
$projectDetail->openProjects($tmpquery);

// Make sure this person has thr right to log hours for this meeting
$teamMember = "false";
$tmpquery = "WHERE tea.project = '" . $meetingDetail->mee_project[0] . "' AND tea.member = '" . $_SESSION['idSession'] . "'";
$memberTest = new request();
$memberTest->openTeams($tmpquery);
$comptMemberTest = count($memberTest->tea_id);

if ($comptMemberTest == "0") {
    $teamMember = "false";
} else {
    $teamMember = "true";
}

if ($teamMember == "false" && $projectsFilter == "true") {
    header("Location:../general/permissiondenied.php");
    exit;
}

//--- header ---
$breadcrumbs[]=buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../projects/viewproject.php?id=" . $projectDetail->pro_id[0], $projectDetail->pro_name[0], LINK_INSIDE);
$breadcrumbs[]=buildLink("../meetings/listmeetings.php?project=" . $projectDetail->pro_id[0], $strings["meetings"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../meetings/viewmeeting.php?id=" . $meetingDetail->mee_id[0], $meetingDetail->mee_name[0], LINK_INSIDE);
$breadcrumbs[]=$strings["add_meeting_time"];

require_once("../themes/" . THEME . "/header.php");

// Check field values
if ($_GET['action'] == 'add') {
    $msgLabel .= ''; // init

    // make sure we have the required information
    if (!empty($hr)) {
        if (!is_numeric($hr)) {
            // we need this to be numeric
            $msgLabel = '<b>' . $strings['attention'] . '</b> : ' . $strings['worked_hours'] . ' ' . $strings['error_numerical'];
        }
    } else {
        // we need this to be numeric
        $msgLabel = '<b>' . $strings['attention'] . '</b> : ' . $strings['worked_hours'] . ' ' . $strings['error_required'];
    }

    // insert meeting time in database
    if (empty($msgLabel)) {
        $comm = addSlashes($comm); // resolves bug #768688
        $tmpquery1 = 'INSERT INTO ' . $tableCollab['meetings_time'] . " (owner,project,meeting,date,hours,comments,created,modified) VALUES ('$owner','" . $meetingDetail->mee_project[0] . "','$id','$ld','$hr','$comm',NOW(),NOW())";
        connectSql($tmpquery1);
        $ld = null;
        $hr = null;
        $comm = null;
        // successful insert
        $msgLabel = '<b>' . $strings['success'] . '</b> : ' . $strings['hours_updated'];
    }
}

$tmpquery1 = "SELECT sum(hours) FROM " . $tableCollab['meetings_time'];

$blockPage=new block();
$blockPage->bornesNumber = "1";
// get actual time for meeting
$meetingActualTime = new request();
$actualTime = $meetingActualTime->getMeetingTime($id);
?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $strings["add_meeting_time"] . " : " . htmlspecialchars($meetingDetail->mee_name[0]); ?></h5>
                    </div>

                    <?php if (!empty($msgLabel)): ?>
                        <div class="card-body">
                            <div class="alert <?php echo strpos($msgLabel, $strings['success']) !== false ? 'alert-success' : 'alert-danger'; ?>">
                                <?php echo $msgLabel; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="../meetings/addmeetingtime.php?id=<?php echo $id; ?>&amp;project=<?php echo htmlspecialchars($projectDetail->pro_name[0]); ?>&amp;action=add" name="saMForm" id="saMForm" class="needs-validation" novalidate>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings["info"]; ?></h6>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><?php echo $strings["project"]; ?> :</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($projectDetail->pro_name[0]); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><?php echo $strings["actual_time"]; ?> :</label>
                                    <div class="form-control-plaintext"><?php echo $actualTime; ?> <?php echo $strings["hours"]; ?></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold"><?php echo $strings["meetings"]; ?> :</label>
                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($meetingDetail->mee_name[0]); ?></div>
                                </div>
                            </div>

                            <?php if (!empty($meetingDetail->mee_agenda[0])): ?>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold"><?php echo $strings["me_agenda"]; ?> :</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <?php echo nl2br(htmlspecialchars($meetingDetail->mee_agenda[0])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <hr class="my-4">

                            <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings["add_meeting_time"]; ?></h6>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label"><?php echo $strings['owner']; ?> :</label>
                                <div class="col-sm-9">
                                    <select name='owner' class='form-select'>
                                        <?php
                                        $tmpquery = "WHERE tea.project = '" . $projectDetail->pro_id[0] . "' ORDER BY mem.name";
                                        $projmem = new request();
                                        $projmem->openTeams($tmpquery);
                                        $comptProjmem = count($projmem->tea_mem_id);

                                        for ($i = 0;$i < $comptProjmem;$i++) {
                                            $clientUser = '';
                                            if ($projmem->tea_mem_profil[$i] == '3') {
                                                $clientUser = ' (' . $strings['client_user'] . ')';
                                            }
                                            $selected = ($_SESSION['nameSession'] == $projmem->tea_mem_name[$i]) ? 'selected' : '';
                                            echo "<option value='" . $projmem->tea_mem_id[$i] . "' $selected>" .
                                                htmlspecialchars($projmem->tea_mem_name[$i]) . "$clientUser</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label"><?php echo $strings['date']; ?> :</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="ld" id="sel1" value="<?php echo htmlspecialchars(empty($ld) ? $date : $ld); ?>">
                                        <button type="button" id="trigger_a" class="btn btn-outline-secondary">...</button>
                                    </div>
                                    <script type="text/javascript">Calendar.setup({ inputField:"sel1", button:"trigger_a" });</script>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label"><?php echo $strings["worked_hours"]; ?> :</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="hr" value="<?php echo htmlspecialchars($hr); ?>" maxlength="6" required>
                                    <div class="invalid-feedback"><?php echo $strings['worked_hours'] . ' ' . $strings['error_required']; ?></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?> :</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="comm" rows="4"><?php echo htmlspecialchars($comm); ?></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-9 offset-sm-3">
                                    <button type="submit" class="btn btn-primary"><?php echo $strings["save"]; ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $strings["meeting_time"] . ' : ' . $strings["details"]; ?></h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $tmpquery = "WHERE mti.meeting = '$id' ORDER BY mti.date DESC";
                        $listMeetingTimes = new request();
                        $listMeetingTimes->openMeetingTime($tmpquery, 0, 10);
                        $comptListMeetingTimes = count($listMeetingTimes->mti_id);

                        if ($comptListMeetingTimes != "0"):
                            ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                    <tr>
                                        <th><?php echo $strings["owner"]; ?></th>
                                        <th><?php echo $strings["date"]; ?></th>
                                        <th><?php echo ucfirst($strings["hours"]); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for ($i = 0;$i < $comptListMeetingTimes;$i++): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($listMeetingTimes->mti_mem_name[$i]); ?></td>
                                            <td><?php echo htmlspecialchars($listMeetingTimes->mti_date[$i]); ?></td>
                                            <td><?php echo htmlspecialchars($listMeetingTimes->mti_hours[$i]); ?></td>
                                        </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($comptListMeetingTimes >= 10): ?>
                            <div class="text-center mt-2">
                                <a href="../meetings/viewmeeting.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary">
                                    <?php echo $strings["view_all"]; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted"><?php echo $strings["no_results"]; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Bootstrap 5 form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var form = document.getElementById('saMForm');
                if (form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                }
            }, false);
        })();

        // Validate hours input
        document.querySelector('input[name="hr"]').addEventListener('input', function(e) {
            var value = e.target.value;
            if (value && !/^\d*\.?\d*$/.test(value)) {
                e.target.setCustomValidity('<?php echo $strings['worked_hours'] . ' ' . $strings['error_numerical']; ?>');
            } else {
                e.target.setCustomValidity('');
            }
        });
    </script>
<?php
require_once("../themes/" . THEME . "/footer.php");
?>