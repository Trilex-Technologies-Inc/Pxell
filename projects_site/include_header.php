<?php
// $Revision: 1.7 $

//--- validate user-status -----------
if ($_SESSION['projectSession'] != "" && $changeProject != "true") {
    $tmpquery = "WHERE pro.id = '" . $_SESSION['projectSession'] . "'";
    $projectDetail = new request();
    $projectDetail->openProjects($tmpquery);

    $tmpquery = "WHERE tea.project = '" . $_SESSION['projectSession'] . "' AND tea.member = '" . $_SESSION['idSession'] . "'";
    $memberTest = new request();
    $memberTest->openTeams($tmpquery);
    $teamMember = count($memberTest->tea_id) > 0 ? true : false;

    if (!$teamMember) {
        header('Location: index.php');
        exit;
    }
}

//--- html-header ---------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?= $setCharset ?>">
    <meta name="robots" content="none">
    <meta name="description" content="<?= $setDescription ?>">
    <meta name="keywords" content="<?= $setKeywords ?>">
    <title>
        <?= $setTitle ?>
        <?php
        if ($_SESSION['projectSession'] != "" && $changeProject != "true") {
            echo " - " . $projectDetail->pro_name[0];
        } else {
            echo " - " . $strings["my_projects"];
        }
        ?>
    </title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../themes/<?= THEME ?>/calendar.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row bg-secondary text-white align-items-center p-3">
        <div class="col-2 text-center">
            <img src="../themes/<?= THEME ?>/spacer.gif" class="img-fluid" alt="">
        </div>
        <div class="col-10">
            <h3 class="mb-0"><?= $titlePage ?></h3>
        </div>
    </div>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 bg-light vh-100 p-3">
            <?php if ($_SESSION['projectSession'] != "" && $changeProject != "true"): ?>
                <h6><?= $strings["project"] ?>: <?= $projectDetail->pro_name[0] ?></h6>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link <?= $bouton[0]=='over'?'active':'' ?>" href="home.php"><?= $strings["home"] ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="showallcontacts.php"><?= $strings["project_team"] ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="showallteamtasks.php"><?= $strings["team_tasks"] ?></a></li>
                    <?php if ($projectDetail->pro_organization[0] != "" && $projectDetail->pro_organization[0] != "1"): ?>
                        <li class="nav-item"><a class="nav-link" href="showallclienttasks.php"><?= $strings["client_tasks"] ?></a></li>
                    <?php endif; ?>
                    <?php if ($fileManagement == "true"): ?>
                        <li class="nav-item"><a class="nav-link" href="doclists.php"><?= $strings["document_list"] ?></a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="showallthreadtopics.php"><?= $strings["bulletin_board"] ?></a></li>
                    <?php if ($enableHelpSupport == "true"): ?>
                        <li class="nav-item"><a class="nav-link" href="showallsupport.php?project=<?= $_SESSION['projectSession'] ?>"><?= $strings["support"] ?></a></li>
                    <?php endif; ?>
                    <li class="nav-item mt-3"><a class="nav-link text-danger" href="../general/login.php?logout=true"><?= $strings["logout"] ?></a></li>
                </ul>
            <?php endif; ?>

            <hr>
            <a class="btn btn-sm btn-outline-primary mb-2 w-100" href="home.php?changeProject=true"><?= $strings["my_projects"] ?></a>
            <a class="btn btn-sm btn-outline-secondary mb-2 w-100" href="changepassword.php"><?= $strings["preferences"] ?></a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">
