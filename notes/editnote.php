<?php // $Revision: 1.5 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: editnote.php,v 1.5 2004/12/15 12:25:19 pixtur Exp $
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

if ($id != "" && $action != "add") {
    $tmpquery = "WHERE note.id = '$id'";
    $noteDetail = new request();
    $noteDetail->openNotes($tmpquery);
    $tmpquery = "WHERE pro.id = '" . $noteDetail->note_project[0] . "'";
    $project = $noteDetail->note_project[0];
    if ($noteDetail->note_owner[0] != $_SESSION['idSession']) {
        header("Location: ../notes/listnotes.php?project=$project&msg=noteOwner");
        exit;
    }
} else {
    $tmpquery = "WHERE pro.id = '$project'";
}

$projectDetail = new request();
$projectDetail->openProjects($tmpquery);

$teamMember = "false";
$tmpquery = "WHERE tea.project = '$project' AND tea.member = '" . $_SESSION['idSession'] . "'";
$memberTest = new request();
$memberTest->openTeams($tmpquery);
$comptMemberTest = count($memberTest->tea_id);
if ($comptMemberTest == "0") {
    $teamMember = "false";
} else {
    $teamMember = "true";
}
// case update note entry
if ($id != "") {
    // case update note entry
    if ($action == "update") {
        $subject = convertData($subject);
        $description = convertData($description);
        $tmpquery5 = "UPDATE " . $tableCollab["notes"] . " SET project='$projectMenu',topic='$topic',subject='$subject',description='$description',date='$dd',owner='" . $_SESSION['idSession'] . "' WHERE id = '$id'";
        $msg = "update";
        connectSql("$tmpquery5");
        header("Location: ../notes/viewnote.php?id=$id&msg=$msg");
        exit;
    }
    // set value in form
    $dd = $noteDetail->note_date[0];
    $subject = $noteDetail->note_subject[0];
    $description = $noteDetail->note_description[0];
    $topic = $noteDetail->note_topic[0];
}
// case add note entry
if ($id == "") {
    // case add note entry
    if ($action == "add") {
        $subject = convertData($subject);
        $description = convertData($description);
        $tmpquery1 = "INSERT INTO " . $tableCollab["notes"] . "(project,topic,subject,description,date,owner,published) VALUES('$projectMenu','$topic','$subject','$description','$dd','" . $_SESSION['idSession'] . "','1')";
        connectSql("$tmpquery1");
        $tmpquery = $tableCollab["notes"];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);
        header("Location: ../notes/viewnote.php?id=$num&msg=add");
        exit;
    }
}



//--- header ---
$breadcrumbs[]=buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../projects/viewproject.php?id=" . $projectDetail->pro_id[0], $projectDetail->pro_name[0], LINK_INSIDE);
$breadcrumbs[]=buildLink("../notes/listnotes.php?project=" . $projectDetail->pro_id[0], $strings["notes"], LINK_INSIDE);
if ($id == "") {
    $breadcrumbs[]=$strings["add_note"];
}
if ($id != "") {
    $breadcrumbs[]=buildLink("../notes/viewnote.php?id=" . $noteDetail->note_id[0], $noteDetail->note_subject[0], LINK_INSIDE);
    $breadcrumbs[]=$strings["edit_note"];
}



$bodyCommand = "onLoad=\"document.etDForm.subject.focus();\"";
require_once("../themes/" . THEME . "/header.php");

//--- content ---
$block1 = new block();
if ($id == "") {
    $block1->form = "etD";
    $block1->openForm("../notes/editnote.php?project=$project&amp;id=$id&amp;action=add#" . $block1->form . "Anchor");
}
if ($id != "") {
    $block1->form = "etD";
    $block1->openForm("../notes/editnote.php?project=$project&amp;id=$id&amp;action=update#" . $block1->form . "Anchor");
}
?>
    <div class="container mt-4">
        <a name="etDAnchor"></a>

        <?php if ($error != ""): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <?php if ($id == ""): ?>
                        <?php echo $strings["add_note"]; ?>
                    <?php else: ?>
                        <?php echo $strings["edit_note"] . " : " . htmlspecialchars($noteDetail->note_subject[0]); ?>
                    <?php endif; ?>
                </h5>
            </div>

            <form method="POST" action="../notes/editnote.php?project=<?php echo $project; ?>&amp;id=<?php echo $id; ?>&amp;action=<?php echo ($id == "") ? 'add' : 'update'; ?>" name="etDForm" id="etDForm">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings["details"]; ?></h6>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["project"]; ?> :</label>
                        <div class="col-sm-9">
                            <select name="projectMenu" class="form-select">
                                <?php
                                $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "' ORDER BY pro.name";
                                $listProjects = new request();
                                $listProjects->openTeams($tmpquery);
                                $comptListProjects = count($listProjects->tea_id);

                                for ($i = 0;$i < $comptListProjects;$i++) {
                                    $selected = ($listProjects->tea_pro_id[$i] == $noteDetail->note_project[0] || $project == $listProjects->tea_pro_id[$i]) ? 'selected' : '';
                                    echo "<option value=\"" . $listProjects->tea_pro_id[$i] . "\" $selected>" . htmlspecialchars($listProjects->tea_pro_name[$i]) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["date"]; ?> :</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="date" class="form-control" name="dd" id="sel3" value="<?php echo htmlspecialchars($dd); ?>">
                                <button type="button" id="trigger_b" class="btn btn-outline-secondary">...</button>
                            </div>
                        </div>
                    </div>

                    <?php
                    $comptTopic = count($topicNote);
                    if ($comptTopic != "0"):
                        ?>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label"><?php echo $strings["topic"]; ?> :</label>
                            <div class="col-sm-9">
                                <select name="topic" class="form-select">
                                    <option value=""><?php echo $strings["choice"]; ?></option>
                                    <?php
                                    for ($i = 1;$i <= $comptTopic;$i++) {
                                        $selected = ($topic == $i) ? 'selected' : '';
                                        echo "<option value=\"$i\" $selected>" . htmlspecialchars($topicNote[$i]) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["subject"]; ?> :</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="subject" value="<?php echo htmlspecialchars($subject); ?>" maxlength="100" autofocus>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["description"]; ?> :</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" rows="6"><?php echo htmlspecialchars($description); ?></textarea>
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

    <script>
        // Auto-focus on subject field
        document.addEventListener('DOMContentLoaded', function() {
            document.etDForm.subject.focus();
        });
    </script>
<?php
$block1->closeContent();
$block1->headingForm_close();
$block1->closeForm();

require_once("../themes/" . THEME . "/footer.php");

?>