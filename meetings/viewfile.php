<?php
// $Revision: 1.4 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: viewfile.php,v 1.4 2004/12/20 23:45:01 pixtur Exp $
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
require_once("../includes/files_types.php");

if ($action == "publish") {
    if ($addToSiteFile == "true") {
        $tmpquery1 = "UPDATE " . $tableCollab["meetings_attachment"] . " SET published='0' WHERE id = '$file' OR vc_parent = '$file'";
        connectSql("$tmpquery1");
        $msg = "addToSite";
        $id = $file;
    }
    if ($removeToSiteFile == "true") {
        $tmpquery1 = "UPDATE " . $tableCollab["meetings_attachment"] . " SET published='1' WHERE id = '$file' OR vc_parent = '$file'";
        connectSql("$tmpquery1");
        $msg = "removeToSite";
        $id = $file;
    }
}

$tmpquery = "WHERE mat.id = '$id'";
$fileDetail = new request();
$fileDetail->openMeetingsAttachment($tmpquery);
$comptFileDetail = count($fileDetail->mat_id);

$teamMember = "false";
$tmpquery = "WHERE tea.project = '" . $fileDetail->mat_project[0] . "' AND tea.member = '" . $_SESSION['idSession'] . "'";
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

$tmpquery = "WHERE pro.id = '" . $fileDetail->mat_project[0] . "'";
$projectDetail = new request();
$projectDetail->openProjects($tmpquery);
$tmpquery = "WHERE mee.id = '" . $fileDetail->mat_meeting[0] . "'";
$meetingDetail = new request();
$meetingDetail->openMeetings($tmpquery);

$type = file_info_type($fileDetail->mat_extension[0]);
$displayname = $fileDetail->mat_name[0];

// ---------------------------------------------------------------------------------------------------
// Update file code
if ($action == "update" && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($maxCustom != "") {
        $maxFileSize = $maxCustom;
    }
    if ($_FILES['upload']['size'] != 0) {
        $taille_ko = $_FILES['upload']['size'] / 1024;
    } else {
        $taille_ko = 0;
    }
    if ($_FILES['upload']['name'] == "") {
        $error4 .= $strings["no_file"] . "<br>";
    }
    if ($_FILES['upload']['size'] > $maxFileSize) {
        if ($maxFileSize != 0) {
            $taille_max_ko = $maxFileSize / 1024;
        }
        $error4 .= $strings["exceed_size"] . " ($taille_max_ko $byteUnits[1])<br>";
    }

    $upload_name = $fileDetail->mat_name[0];
    $extension = strtolower(substr(strrchr($upload_name, ".") , 1));
    $changename = str_replace(".", " v" . $fileDetail->mat_vc_version[0] . ".", $fileDetail->mat_name[0]);
    $path = "files/" . $fileDetail->mat_project[0] . "/meetings/" . $fileDetail->mat_meeting[0] . "/$upload_name";
    $path_source = "files/" . $fileDetail->mat_project[0] . "/meetings/" . $fileDetail->mat_meeting[0] . "/" . $fileDetail->mat_name[0];
    $path_destination = "files/" . $fileDetail->mat_project[0] . "/meetings/" . $fileDetail->mat_meeting[0] . "/$changename";

    if ($allowPhp == "false") {
        $send = "";
        if ($_FILES['upload']['name'] != "" && ($extension == "php" || $extension == "php3" || $extension == "phtml")) {
            $error4 .= $strings["no_php"] . "<br>";
            $send = "false";
        }
    }

    if ($_FILES['upload']['name'] != "" && $_FILES['upload']['size'] < $maxFileSize && $_FILES['upload']['size'] != 0 && $send != "false") {
        $cpy = "true";
    }

    if ($cpy == "true") {
        moveFile($path_source, $path_destination);
        $cpy_project = $fileDetail->mat_project[0];
        $cpy_meeting = $fileDetail->mat_meeting[0];
        $cpy_date = $fileDetail->mat_date[0];
        $cpy_size = $fileDetail->mat_size[0];
        $cpy_extension = $fileDetail->mat_extension[0];
        $cpy_comments = $fileDetail->mat_comments[0];
        $cpy_comments_approval = $fileDetail->mat_comments_approval[0];
        $cpy_approver = $fileDetail->mat_approver[0];
        $cpy_date_approval = $fileDetail->mat_date_approval[0];
        $cpy_upload = $fileDetail->mat_upload[0];
        $cpy_pusblished = $fileDetail->mat_published[0];
        $cpy_vc_parent = $fileDetail->mat_vc_parent[0];
        $cpy_id = $fileDetail->mat_id[0];
        $cpy_vc_version = $fileDetail->mat_vc_version[0];

        $cpy_comments = convertData($cpy_comments);
        $tmpquery = "INSERT INTO " . $tableCollab["meetings_attachment"] . "(owner,project,meeting,name,date,size,extension,comments,comments_approval,approver,date_approval,upload,published,status,vc_status,vc_version,vc_parent) VALUES('" . $_SESSION['idSession'] . "','$cpy_project','$cpy_meeting','$changename','$cpy_date','$cpy_size','$cpy_extension','$cpy_comments','$cpy_comments_approval','$cpy_approver','$cpy_date_approval','$cpy_upload','$cpy_pusblished','2','3','$cpy_vc_version','$cpy_id')";
        connectSql("$tmpquery");
        $tmpquery = $tableCollab["meetings_attachment"];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);
    }

    if ($cpy == "true") {
        uploadFile(".", $_FILES['upload']['tmp_name'], $path);
        $chaine = strrev("$path");
        $tab = explode(".", $chaine);
        $extension = strtolower(strrev($tab[0]));
    }

    $newversion = $fileDetail->mat_vc_version[0] + $change_file_version;
    if ($cpy == "true") {
        $name = $upload_name;
        $tmpquery = "UPDATE " . $tableCollab["meetings_attachment"] . " SET date='$dateheure',size='$size',comments='$c',comments_approval='',approver='',date_approval='',status='$statusField',vc_version='$newversion' WHERE id = '$id'";
        connectSql($tmpquery);
        header('Location: ../meetings/viewfile.php?id=' . $fileDetail->mat_id[0] . '&msg=addFile');
        exit;
    }
}
// ---------------------------------------------------------------------------------------------------
// Add new revision code
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
        $error3 .= $strings["no_file"] . "<br>";
    }
    if ($_FILES['upload']['size'] > $maxFileSize) {
        if ($maxFileSize != 0) {
            $taille_max_ko = $maxFileSize / 1024;
        }
        $error3 .= $strings["exceed_size"] . " ($taille_max_ko $byteUnits[1])<br>";
    }

    $upload_name = $filename;
    $upload_name = str_replace(".", " v$oldversion r$revision.", $upload_name);
    $extension = strtolower(substr(strrchr($upload_name, ".") , 1));

    if ($allowPhp == "false") {
        $send = "";
        if ($_FILES['upload']['name'] != "" && ($extension == "php" || $extension == "php3" || $extension == "phtml")) {
            $error3 .= $strings["no_php"] . "<br>";
            $send = "false";
        }
    }

    if ($_FILES['upload']['name'] != "" && $_FILES['upload']['size'] < $maxFileSize && $_FILES['upload']['size'] != 0 && $send != "false") {
        $cpy = "true";
    }

    if ($cpy == "true") {
        $c = convertData($c);
        $tmpquery = "INSERT INTO " . $tableCollab["meetings_attachment"] . "(owner,project,meeting,comments,upload,published,status,vc_status,vc_parent) VALUES('" . $_SESSION['idSession'] . "','$project','$meeting','$c','$dateheure','$published','2','0','$parent')";
        connectSql("$tmpquery");
        $tmpquery = $tableCollab["meetings_attachment"];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);
    }

    if ($cpy == "true") {
        uploadFile("files/$project/meetings/$meeting", $_FILES['upload']['tmp_name'], $upload_name);
        $size = file_info_size("../files/$project/meetings/$meeting/$upload_name");
        $chaine = strrev("../files/$project/meetings/$meeting/$upload_name");
        $tab = explode(".", $chaine);
        $extension = strtolower(strrev($tab[0]));
    }

    if ($cpy == "true") {
        $name = $upload_name;
        $tmpquery = "UPDATE " . $tableCollab["meetings_attachment"] . " SET name='$name',date='$dateheure',size='$size',extension='$extension',vc_version='$oldversion' WHERE id = '$num'";
        connectSql("$tmpquery");
        header("Location: ../meetings/viewfile.php?id=$sendto&msg=addFile");
        exit;
    }
}

//---- header ---------------------------------------------------------------------------------
$breadcrumbs[]=buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../projects/viewproject.php?id=" . $fileDetail->mat_project[0], $projectDetail->pro_name[0], LINK_INSIDE);
$breadcrumbs[]=buildLink("../meetings/listmeetings.php?project=" . $fileDetail->mat_project[0], $strings["meetings"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../meetings/viewmeeting.php?id=" . $meetingDetail->mee_id[0], $meetingDetail->mee_name[0], LINK_INSIDE);
$breadcrumbs[]=$fileDetail->mat_name[0];

require_once("../themes/" . THEME . "/header.php");

// ---- content -------------------------------------------------------------------------------
// File details block
$block1 = new block();
$block1->form = "vdC";
$block1->openForm("../meetings/viewfile.php?id=$id#" . $block1->form . "Anchor");

$block1->heading($strings["document"]);

if ($fileDetail->mat_owner[0] == $_SESSION['idSession']) {
    $block1->openPaletteIcon();
    $block1->paletteIcon(0, "remove", $strings["ifc_delete_version"]);
    $block1->paletteIcon(1, "add_projectsite", $strings["add_project_site"]);
    $block1->paletteIcon(2, "remove_projectsite", $strings["remove_project_site"]);
    $block1->closePaletteIcon();
} else {
    $block1->heading_close();
}
if ($error1 != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error1);
}

$block1->openContent();
$block1->contentTitle($strings["details"]);
?>

    <div class="file-details">
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["type"]; ?>:</label>
            <div class="col-sm-9">
                <img src="../interface/icones/<?php echo $type; ?>" border="0" alt="">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["name"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo $fileDetail->mat_name[0]; ?>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["vc_version"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo $fileDetail->mat_vc_version[0]; ?>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["ifc_last_date"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo $fileDetail->mat_date[0]; ?>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["size"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo convertSize($fileDetail->mat_size[0]); ?>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["owner"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo buildLink("../users/viewuser.php?id=" . $fileDetail->mat_mem_id[0], $fileDetail->mat_mem_name[0], LINK_INSIDE); ?>
                (<?php echo buildLink($fileDetail->mat_mem_email_work[0], $fileDetail->mat_mem_login[0], LINK_MAIL); ?>)
            </div>
        </div>

        <?php if ($fileDetail->mat_comments[0] != "") : ?>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?>:</label>
                <div class="col-sm-9">
                    <?php echo nl2br($fileDetail->mat_comments[0]); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $idPublish = $fileDetail->mat_published[0];
        ?>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["published"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo $statusPublish[$idPublish]; ?>
            </div>
        </div>

        <?php
        $idStatus = $fileDetail->mat_status[0];
        ?>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["approval_tracking"]; ?>:</label>
            <div class="col-sm-9">
                <?php echo $statusFile[$idStatus]; ?>
            </div>
        </div>

        <?php if ($fileDetail->mat_mem2_id[0] != "") : ?>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["approver"]; ?>:</label>
                <div class="col-sm-9">
                    <?php echo buildLink("../users/viewuser.php?id=" . $fileDetail->mat_mem2_id[0], $fileDetail->mat_mem2_name[0], LINK_INSIDE); ?>
                    (<?php echo buildLink($fileDetail->mat_mem2_email_work[0], $fileDetail->mat_mem2_login[0], LINK_MAIL); ?>)
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["approval_date"]; ?>:</label>
                <div class="col-sm-9">
                    <?php echo $fileDetail->mat_date_approval[0]; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($fileDetail->mat_comments_approval[0] != "") : ?>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"><?php echo $strings["approval_comments"]; ?>:</label>
                <div class="col-sm-9">
                    <?php echo nl2br($fileDetail->mat_comments_approval[0]); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $tmpquery = "WHERE mat.id = '$id' OR mat.vc_parent = '$id' AND mat.vc_status = '3' ORDER BY mat.date DESC";
        $listVersions = new request();
        $listVersions->openMeetingsAttachment($tmpquery);
        $comptListVersions = count($listVersions->mat_vc_parent);
        ?>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["ifc_version_history"]; ?>:</label>
            <div class="col-sm-9">
                <div class="version-history">
                    <?php for ($i = 0; $i < $comptListVersions; $i++) : ?>
                        <div class="version-item mb-3 p-3 border rounded">
                            <div class="version-header d-flex align-items-center mb-2">
                                <?php if ($fileDetail->mat_owner[0] == $_SESSION['idSession'] && $listVersions->mat_id[$i] != $fileDetail->mat_id[0]) : ?>
                                    <div class="me-2">
                                        <input type="checkbox" name="<?php echo $listVersions->mat_id[$i]; ?>" id="<?php echo $block1->form . "cb" . $listVersions->mat_id[$i]; ?>">
                                    </div>
                                <?php endif; ?>

                                <div class="me-3">
                                    <strong><?php echo $strings["vc_version"]; ?>:</strong> <?php echo $listVersions->mat_vc_version[$i]; ?>
                                </div>

                                <div class="me-3 flex-grow-1">
                                    <strong><?php echo $displayname; ?></strong>
                                </div>

                                <div class="me-3">
                                    <?php if (file_exists("../files/" . $listVersions->mat_project[$i] . "/meetings/" . $listVersions->mat_meeting[$i] . "/" . $listVersions->mat_name[$i])) : ?>
                                        <?php echo buildLink("../meetings/accessfile.php?mode=view&amp;id=" . $listVersions->mat_id[$i], $strings["view"], LINK_INSIDE); ?>
                                        <?php $folder = $listVersions->mat_project[$i] . "/meetings/" . $listVersions->mat_meeting[$i]; ?>
                                        <?php echo " " . buildLink("../meetings/accessfile.php?mode=download&amp;id=" . $listVersions->mat_id[$i], $strings["save"], LINK_INSIDE); ?>
                                    <?php else : ?>
                                        <span class="text-danger"><?php echo $strings["missing_file"]; ?></span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <strong><?php echo $strings["date"]; ?>:</strong> <?php echo $listVersions->mat_date[$i]; ?>
                                </div>
                            </div>

                            <?php if ($listVersions->mat_mem2_id[$i] != "" || $listVersions->mat_comments_approval[$i] != "") : ?>
                                <?php $idStatus = $listVersions->mat_status[$i]; ?>
                                <div class="version-approval mt-2 p-2 bg-light">
                                    <?php if ($listVersions->mat_mem2_id[$i] != "") : ?>
                                        <div class="mb-1">
                                            <strong><?php echo $strings["approver"]; ?>:</strong>
                                            <?php echo buildLink("../users/viewuser.php?id=" . $listVersions->mat_mem2_id[$i], $listVersions->mat_mem2_name[$i], LINK_INSIDE); ?>
                                            (<?php echo buildLink($listVersions->mat_mem2_email_work[$i], $listVersions->mat_mem2_login[$i], LINK_MAIL); ?>)
                                        </div>
                                        <div class="mb-1">
                                            <strong><?php echo $strings["approval_tracking"]; ?>:</strong> <?php echo $statusFile[$idStatus]; ?>
                                        </div>
                                        <div class="mb-1">
                                            <strong><?php echo $strings["approval_date"]; ?>:</strong> <?php echo $listVersions->mat_date_approval[$i]; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($listVersions->mat_comments_approval[$i] != "") : ?>
                                        <div class="mt-2">
                                            <strong><?php echo $strings["approval_comments"]; ?>:</strong> <?php echo nl2br($listVersions->mat_comments_approval[$i]); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

<?php
$block1->closeContent();
$block1->headingForm_close();
$block1->closeFormResults();

if ($fileDetail->mat_owner[0] == $_SESSION['idSession']) {
    $block1->openPaletteScript();
    $block1->paletteScript(0, "remove", "../meetings/deletefiles.php?project=" . $fileDetail->mat_project[0] . "&meeting=" . $fileDetail->mat_meeting[0] . "&sendto=filedetails", "false,true,true", $strings["ifc_delete_version"]);
    $block1->paletteScript(1, "add_projectsite", "../meetings/viewfile.php?addToSiteFile=true&file=" . $fileDetail->mat_id[0] . "&action=publish", "true,true,true", $strings["add_project_site"]);
    $block1->paletteScript(2, "remove_projectsite", "../meetings/viewfile.php?removeToSiteFile=true&file=" . $fileDetail->mat_id[0] . "&action=publish", "true,true,true", $strings["remove_project_site"]);
    $block1->closePaletteScript($comptFileDetail, $fileDetail->mat_id);
}

if ($peerReview == "true") {
    // Revision list block
    $block2 = new block();

    $block2->form = "tdC";
    $block2->openForm("../meetings/viewfile.php?id=$id#" . $block2->form . "Anchor");
    $block2->heading($strings["ifc_revisions"]);

    if ($fileDetail->mat_owner[0] == $_SESSION['idSession']) {
        $block2->openPaletteIcon();
        $block2->paletteIcon(0, "remove", $strings["ifc_delete_review"]);
        $block2->closePaletteIcon();
    }

    if ($error2 != "") {
        $block2->headingError($strings["errors"]);
        $block2->contentError($error2);
    }
    $block2->openContent();
    $block2->contentTitle($strings["details"]);
    ?>

    <div class="revision-list">
        <?php
        $tmpquery = "WHERE mat.vc_parent = '$id' AND mat.vc_status != '3' ORDER BY mat.date";
        $listReviews = new request();
        $listReviews->openMeetingsAttachment($tmpquery);
        $comptListReviews = count($listReviews->mat_vc_parent);

        for ($i = 0; $i < $comptListReviews; $i++) {
            $displayrev = $i + 1;
            ?>
            <div class="revision-item mb-3 p-3 border rounded">
                <div class="revision-header d-flex align-items-center mb-2">
                    <?php if ($fileDetail->mat_owner[0] == $_SESSION['idSession']) : ?>
                        <div class="me-2">
                            <input type="checkbox" name="<?php echo $listReviews->mat_id[$i]; ?>" id="<?php echo $block2->form . "cb" . $listReviews->mat_id[$i]; ?>">
                        </div>
                    <?php endif; ?>

                    <div class="me-3 flex-grow-1">
                        <strong><?php echo $displayname; ?></strong>
                    </div>

                    <div class="me-3">
                        <?php if (file_exists("../files/" . $listReviews->mat_project[$i] . "/meetings/" . $listReviews->mat_meeting[$i] . "/" . $listReviews->mat_name[$i])) : ?>
                            <?php echo buildLink("../meetings/accessfile.php?mode=view&amp;id=" . $listReviews->mat_id[$i], $strings["view"], LINK_INSIDE); ?>
                            <?php $folder = $listReviews->mat_project[$i] . "/meetings/" . $listReviews->mat_meeting[$i]; ?>
                            <?php echo " " . buildLink("../meetings/accessfile.php?mode=download&amp;id=" . $listReviews->mat_id[$i], $strings["save"], LINK_INSIDE); ?>
                        <?php else : ?>
                            <span class="text-danger"><?php echo $strings["missing_file"]; ?></span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <strong>Revision:</strong> <?php echo $displayrev; ?>
                    </div>
                </div>

                <div class="revision-details d-flex mb-2">
                    <div class="me-4">
                        <strong><?php echo $strings["ifc_revision_of"]; ?>:</strong> <?php echo $listReviews->mat_vc_version[$i]; ?>
                    </div>
                    <div class="me-4">
                        <strong><?php echo $strings["owner"]; ?>:</strong> <?php echo $listReviews->mat_mem_name[$i]; ?>
                    </div>
                    <div>
                        <strong><?php echo $strings["date"]; ?>:</strong> <?php echo $listReviews->mat_date[$i]; ?>
                    </div>
                </div>

                <div class="revision-comments">
                    <strong><?php echo $strings["comments"]; ?>:</strong> <?php echo $listReviews->mat_comments[$i]; ?>
                </div>
            </div>
            <?php
        }

        if ($comptListReviews == 0) {
            echo '<div class="alert alert-warning">' . $strings["ifc_no_revisions"] . '</div>';
        }
        ?>
    </div>

    <?php
    $block2->closeContent();
    $block2->headingForm_close();
    $block2->closeFormResults();

    if ($fileDetail->mat_owner[0] == $_SESSION['idSession']) {
        $block2->openPaletteScript();
        $block2->paletteScript(0, "remove", "../meetings/deletefiles.php?project=" . $fileDetail->mat_project[0] . "&meeting=" . $fileDetail->mat_meeting[0] . "&sendto=filedetails", "false,true,true", $strings["ifc_delete_review"]);
        $block2->closePaletteScript($comptListReviews, $listReviews->mat_id);
    }

    if ($teamMember == "true" || $_SESSION['profilSession'] == "5") {
        // Add new revision Block
        $block3 = new block();
        $block3->form = "filedetails";
        ?>
        <a name="filedetailsAnchor"></a>
        <form method="POST" action="../meetings/viewfile.php?action=add&amp;id=<?php echo $fileDetail->mat_id[0]; ?>#filedetailsAnchor" name="filedetailsForm" enctype="multipart/form-data">
        <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
        <input type="hidden" name="maxCustom" value="<?php echo $projectDetail->pro_upload_max[0]; ?>">

        <?php
        if ($error3 != "") {
            $block3->headingError($strings["errors"]);
            $block3->contentError($error3);
        }
        $block3->headingForm($strings["ifc_add_revision"]);
        $block3->openContent();
        $block3->contentTitle($strings["details"]);

        $revision = $comptListReviews + 1;
        ?>

        <input value="<?php echo $fileDetail->mat_id[0]; ?>" name="sendto" type="hidden">
        <input value="<?php echo $fileDetail->mat_id[0]; ?>" name="parent" type="hidden">
        <input value="<?php echo $revision; ?>" name="revision" type="hidden">
        <input value="<?php echo $fileDetail->mat_vc_version[0]; ?>" name="oldversion" type="hidden">
        <input value="<?php echo $fileDetail->mat_project[0]; ?>" name="project" type="hidden">
        <input value="<?php echo $fileDetail->mat_meeting[0]; ?>" name="meeting" type="hidden">
        <input value="<?php echo $fileDetail->mat_published[0]; ?>" name="published" type="hidden">
        <input value="<?php echo $fileDetail->mat_name[0]; ?>" name="filename" type="hidden">

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">* <?php echo $strings["upload"]; ?>:</label>
            <div class="col-sm-9">
                <input name="upload" type="file" class="form-control">
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?>:</label>
            <div class="col-sm-9">
                <textarea name="c" rows="3" class="form-control"><?php echo $c; ?></textarea>
            </div>
        </div>

        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">&nbsp;</label>
            <div class="col-sm-9">
                <input type="submit" value="<?php echo $strings["save"]; ?>" class="btn btn-primary">
            </div>
        </div>

        <?php
        $block3->closeContent();
        $block3->headingForm_close();
        $block3->closeFormResults();
    }
}

// Update file Block
if ($fileDetail->mat_owner[0] == $_SESSION['idSession']) {
    $block4 = new block();
    ?>
    <a name="filedetailsAnchor"></a>
<form method="POST" action="../meetings/viewfile.php?action=update&amp;id=<?php echo $fileDetail->mat_id[0]; ?>#filedetailsAnchor" name="filedetailsForm" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
    <input type="hidden" name="maxCustom" value="<?php echo $projectDetail->pro_upload_max[0]; ?>">

    <?php
    if ($error4 != "") {
        $block4->headingError($strings["errors"]);
        $block4->contentError($error4);
    }

    $block4->headingForm($strings["ifc_update_file"]);
    $block4->openContent();
    $block4->contentTitle($strings["details"]);
    ?>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?php echo $strings["version_increm"]; ?>:</label>
        <div class="col-sm-9">
            <div class="version-options">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="change_file_version" value="0.01" id="version001">
                    <label class="form-check-label" for="version001">0.01</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="change_file_version" value="0.1" id="version01" checked>
                    <label class="form-check-label" for="version01">0.1</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="change_file_version" value="1.0" id="version10">
                    <label class="form-check-label" for="version10">1.0</label>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?php echo $strings["status"]; ?>:</label>
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
        <label class="col-sm-3 col-form-label">* <?php echo $strings["upload"]; ?>:</label>
        <div class="col-sm-9">
            <input name="upload" type="file" class="form-control">
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?>:</label>
        <div class="col-sm-9">
            <textarea name="c" rows="3" class="form-control"><?php echo $c; ?></textarea>
        </div>
    </div>

    <div class="row mb-3">
        <label class="col-sm-3 col-form-label">&nbsp;</label>
        <div class="col-sm-9">
            <input type="submit" value="<?php echo $strings["ifc_update_file"]; ?>" class="btn btn-primary">
        </div>
    </div>

    <?php
    $block4->closeContent();
    $block4->headingForm_close();
    $block4->closeFormResults();
}

require_once("../themes/" . THEME . "/footer.php");
?>
