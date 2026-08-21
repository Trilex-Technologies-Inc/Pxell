<?php // $Revision: 1.3 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: addfile.php,v 1.3 2004/12/15 12:25:11 pixtur Exp $
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

if ($action == "add" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($maxCustom != "") {
        $maxFileSize = $maxCustom;
    } 
    if ($_FILES['upload']['size'] != 0) {
        $taille_ko = $_FILES['upload']['size'] / 1024;
    } else {
        $taille_ko = 0;
    } 
    if ($_FILES['upload']['name'] == "") {
        $error .= $strings["no_file"] . "<br>";
    } 
    if ($_FILES['upload']['size'] > $maxFileSize) {
        if ($maxFileSize != 0) {
            $taille_max_ko = $maxFileSize / 1024;
        } 
        $error .= $strings["exceed_size"] . " ($taille_max_ko $byteUnits[1])<br>";
    } 

    $extension = strtolower(substr(strrchr($_FILES['upload']['name'], ".") , 1));
    $extension_len = strlen($extension) + 1;
    $filename_len = strlen($_FILES['upload']['name']);
    $filename_prefix = substr($_FILES['upload']['name'], 0, $filename_len - $extension_len);

    if ($allowPhp == "false") {
        $send = "";
        if ($_FILES['upload']['name'] != "" && ($extension == "php" || $extension == "php3" || $extension == "phtml")) {
            $error .= $strings["no_php"] . "<br>";
            $send = "false";
        } 
    } 
    if ($_FILES['upload']['name'] != "" && $_FILES['upload']['size'] < $maxFileSize && $_FILES['upload']['size'] != 0 && $send != "false") {
        $cpy = "true";
    } 
    if ($cpy == "true") {
        $match = strstr($versionFile, ".");
        if ($match == "") {
            $versionFile = $versionFile . ".0";
        } 

        if ($versionFile == "") {
            $versionFile = "0.0";
        } 
        $c = convertData($c);
        $tmpquery = "INSERT INTO " . $tableCollab["meetings_attachment"] . "(owner,project,meeting,comments,upload,published,status,vc_version,vc_parent) VALUES('" . $_SESSION['idSession'] . "','$project','$meeting','$c','$dateheure','1','$statusField','$versionFile','0')";
        connectSql("$tmpquery");
        $tmpquery = $tableCollab["meetings_attachment"];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);
    } 

    if ($cpy == "true") {
        uploadFile("files/$project/meetings/$meeting", $_FILES['upload']['tmp_name'], $filename_prefix . "--$num." . $extension);
        $size = file_info_size("../files/" . $project . "/meetings/" . $meeting . "/" . $filename_prefix . "--$num." . $extension);
        $chaine = strrev("../files/" . $project . "/meetings/" . $meeting . "/" . $filename_prefix . "--$num." . $extension);
        $tab = explode(".", $chaine);
        $extension = strtolower(strrev($tab[0]));
    } 
    if ($cpy == "true") {
        $name = $filename_prefix . "--$num." . $extension;
        $tmpquery = "UPDATE " . $tableCollab["meetings_attachment"] . " SET name='$name',date='$dateheure',size='$size',extension='$extension' WHERE id = '$num'";
        connectSql("$tmpquery");
        header("Location: ../meetings/viewfile.php?id=$num&msg=addFile");
        exit;
    } 
} 

$tmpquery = "WHERE pro.id = '$project'";
$projectDetail = new request();
$projectDetail->openProjects($tmpquery);

$tmpquery = "WHERE mee.id = '$meeting'";
$meetingDetail = new request();
$meetingDetail->openMeetings($tmpquery);

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

if ($teamMember == "false" && $projectsFilter == "true") {
    header('Location: ../general/permissiondenied.php');
    exit;
} 

//--- header ----------------------------------------------------------------------------------
$breadcrumbs[]=buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../projects/viewproject.php?id=$project", $projectDetail->pro_name[0], LINK_INSIDE);
$breadcrumbs[]=buildLink("../meetings/listmeetings.php?$project=$project", $strings["meetings"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../tasks/viewmeeting.php?id=$meeting", $meetingDetail->mee_name[0], LINK_INSIDE);
$breadcrumbs[]=$strings["add_file"];

require_once("../themes/" . THEME . "/header.php");


//--- content ---------------------------------------------------------------------------------
$block1 = new block();
?>
    <div class="container mt-4">
        <?php if ($error != ''): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $strings['add_file']; ?></h5>
            </div>

            <form method="POST" action="../meetings/addfile.php?action=add&amp;project=<?php echo $project; ?>&amp;meeting=<?php echo $meeting; ?>" name="filedetailsForm" enctype="multipart/form-data" class="needs-validation" novalidate>
                <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
                <input type="hidden" name="maxCustom" value="<?php echo $projectDetail->pro_upload_max[0]; ?>">

                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings['details']; ?></h6>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings['status']; ?> :</label>
                        <div class="col-sm-9">
                            <select name="statusField" class="form-select">
                                <?php
                                $comptSta = count($statusFile);
                                for ($i = 0; $i < $comptSta; $i++):
                                    $selected = ($i == "2") ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo $i; ?>" <?php echo $selected; ?>>
                                        <?php echo $statusFile[$i]; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">
                            <span class="text-danger">*</span> <?php echo $strings['upload']; ?> :
                        </label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control" name="upload" required>
                            <div class="invalid-feedback">
                                <?php echo $strings['please_select_a_file']; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings['comments']; ?> :</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="c" rows="3"><?php echo htmlspecialchars($c); ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings['vc_version']; ?> :</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="versionFile" value="0.0">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $strings['save']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php
require_once("../themes/" . THEME . "/footer.php");
?>
