<?php // $Revision: 1.6 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: addfile.php,v 1.6 2004/12/15 21:21:20 madbear Exp $
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

// set task to "0" for project main folder upload
if ($task == "") {
    $task = "0";
}

if ($action == "add") {
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
        $tmpquery = "INSERT INTO " . $tableCollab["files"] . "(owner,project,phase,task,comments,upload,published,status,vc_version,vc_parent) VALUES('" . $_SESSION['idSession'] . "','$project','$phase','$task','$c','$dateheure','1','$statusField','$versionFile','0')";
        connectSql("$tmpquery");
        $tmpquery = $tableCollab["files"];
        last_id($tmpquery);
        $num = $lastId[0];
        unset($lastId);
    }

    if ($task != "0") {
        if ($cpy == "true") {
            uploadFile("files/$project/$task", $_FILES['upload']['tmp_name'], "$num--" . $_FILES['upload']['name']);
            $size = file_info_size("../files/" . $project . "/" . $task . "/" . $num . "--" . $_FILES['upload']['name']);
            // $dateFile = file_info_date("../files/".$project."/".$task."/".$num."--".$_FILES['upload']['name']);
            $chaine = strrev("../files/" . $project . "/" . $task . "/" . $num . "--" . $_FILES['upload']['name']);
            $tab = explode(".", $chaine);
            $extension = strtolower(strrev($tab[0]));
        }
    } else {
        if ($cpy == "true") {
            uploadFile("files/$project", $_FILES['upload']['tmp_name'], "$num--" . $_FILES['upload']['name']);
            $size = file_info_size("../files/" . $project . "/" . $num . "--" . $_FILES['upload']['name']);
            // $dateFile = file_info_date("../files/".$project."/".$num."--".$_FILES['upload']['name']);
            $chaine = strrev("../files/" . $project . "/" . $num . "--" . $_FILES['upload']['name']);
            $tab = explode(".", $chaine);
            $extension = strtolower(strrev($tab[0]));
        }
    }
    if ($cpy == "true") {
        $name = $num . "--" . $_FILES['upload']['name'];
        $tmpquery = "UPDATE " . $tableCollab["files"] . " SET name='$name',date='$dateheure',size='$size',extension='$extension' WHERE id = '$num'";
        connectSql("$tmpquery");
        header("Location: ../linkedcontent/viewfile.php?id=$num&msg=addFile");
        exit;
    }
}

$tmpquery = "WHERE pro.id = '$project'";
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

if ($teamMember == "false" && $projectsFilter == "true") {
    header('Location: ../general/permissiondenied.php');
    exit;
}

if ($projectDetail->pro_phase_set[0] != "0") {
    $tmpquery = "WHERE pha.id = '$phase'";
    $phaseDetail = new request();
    $phaseDetail->openPhases($tmpquery);
}

if ($task != "0") {
    $tmpquery = "WHERE tas.id = '$task'";
    $taskDetail = new request();
    $taskDetail->openTasks($tmpquery);
}



//--- header ---
$breadcrumbs[]=buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[]=buildLink("../projects/viewproject.php?id=$project", $projectDetail->pro_name[0], LINK_INSIDE);

if ($projectDetail->pro_phase_set[0] != "0" && $phase != 0) {
    $breadcrumbs[]=buildLink("../phases/viewphase.php?id=" . $phaseDetail->pha_id[0], $phaseDetail->pha_name[0], LINK_INSIDE);
}

if ($task != "0") {
    $breadcrumbs[]=buildLink("../tasks/listtasks.php?$project=$project", $strings["tasks"], LINK_INSIDE);
    $breadcrumbs[]=buildLink("../tasks/viewtask.php?id=$task", $taskDetail->tas_name[0], LINK_INSIDE);
}

$breadcrumbs[]=$strings["add_file"];



require_once("../themes/" . THEME . "/header.php");

//---- content -------
$block1 = new block();

$block1->form = "filedetails";
?>
    <div class="container mt-4">
        <a name="filedetailsAnchor"></a>

        <form method="POST" action="../linkedcontent/addfile.php?action=add&amp;project=<?php echo $project; ?>&amp;task=<?php echo $task; ?>&amp;phase=<?php echo $phase; ?>" name="filedetailsForm" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="MAX_FILE_SIZE" value="100000000">
            <input type="hidden" name="maxCustom" value="<?php echo $projectDetail->pro_upload_max[0]; ?>">

            <?php if ($error != ""): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $strings["add_file"]; ?></h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted"><?php echo $strings["details"]; ?></h6>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["status"]; ?> :</label>
                        <div class="col-sm-9">
                            <select name="statusField" class="form-select">
                                <?php
                                $comptSta = count($statusFile);
                                for ($i = 0;$i < $comptSta;$i++) {
                                    if ($i == "2") {
                                        echo "<option value=\"$i\" selected>$statusFile[$i]</option>";
                                    } else {
                                        echo "<option value=\"$i\">$statusFile[$i]</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">
                            <span class="text-danger">*</span> <?php echo $strings["upload"]; ?> :
                        </label>
                        <div class="col-sm-9">
                            <input class="form-control" type="file" name="upload" required>
                            <div class="form-text">
                                <?php
                                $maxFileSizeKB = $projectDetail->pro_upload_max[0] / 1024;
                                echo sprintf($strings["max_file_size"], $maxFileSizeKB, $byteUnits[1]);
                                ?>
                                <?php if ($allowPhp == "false"): ?>
                                    <br><?php echo $strings["php_files_not_allowed"]; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["comments"]; ?> :</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="c" rows="3"><?php echo htmlspecialchars($c); ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label"><?php echo $strings["vc_version"]; ?> :</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="versionFile" value="0.0" pattern="\d+(\.\d+)?" title="<?php echo $strings["version_format_hint"]; ?>">
                            <div class="form-text"><?php echo $strings["version_format_example"]; ?>: 1.0, 2.1, etc.</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-9 offset-sm-3">
                            <button type="submit" class="btn btn-primary"><?php echo $strings["save"]; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Bootstrap 5 form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // File size validation
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            var maxSize = <?php echo $projectDetail->pro_upload_max[0]; ?>;
            var file = e.target.files[0];

            if (file && file.size > maxSize) {
                alert('<?php echo $strings["exceed_size"]; ?> (<?php echo $projectDetail->pro_upload_max[0] / 1024; ?> <?php echo $byteUnits[1]; ?>)');
                e.target.value = '';
            }

            // PHP file validation
            <?php if ($allowPhp == "false"): ?>
            var fileName = file.name.toLowerCase();
            var ext = fileName.split('.').pop();
            if (ext === 'php' || ext === 'php3' || ext === 'phtml') {
                alert('<?php echo $strings["no_php"]; ?>');
                e.target.value = '';
            }
            <?php endif; ?>
        });
    </script>
<?php
$block1->closeForm();
require_once("../themes/" . THEME . "/footer.php");
?>