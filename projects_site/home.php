<?php
$checkSession = true;
require_once("../includes/library.php");

if ($updateProject == "true") {
    $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "' AND pro.id = '$project' AND pro.status IN(0,2,3) AND pro.published = '0'";
    $testProject = new request();
    $testProject->openTeams($tmpquery);
    $comptTestProject = count($testProject->tea_id);

    if ($comptTestProject != "0") {
        $_SESSION['projectSession'] = $_GET['project'];
        header("Location: home.php");
        exit;
    } else {
        header("Location: home.php?changeProject=true");
        exit;
    }
}

$bouton[0] = "over";
$titlePage = $strings["welcome"] . " " . $_SESSION['nameSession'] . " " . $strings["your_projectsite"];
require_once("include_header.php");

// Load organization details if project is set
if ($updateProject != "true" && $changeProject != "true") {
    $tmpquery = "WHERE org.id = '" . $projectDetail->pro_organization[0] . "'";
    $clientDetail = new request();
    $clientDetail->openOrganizations($tmpquery);
}

// Show project list if no project is selected or user changes project
if ($_SESSION['projectSession'] == "" || $changeProject == "true") {
    $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "' AND pro.status IN(0,2,3) AND pro.published = '0' ORDER BY pro.name";
    $listProjects = new request();
    $listProjects->openTeams($tmpquery);
    $comptListProjects = count($listProjects->tea_id);
    ?>

    <h4><?= $strings["my_projects"] ?></h4>

    <?php if ($comptListProjects != 0): ?>
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php for ($i=0; $i<$comptListProjects; $i++): ?>
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="home.php?updateProject=true&project=<?= $listProjects->tea_pro_id[$i] ?>">
                                    <?= $listProjects->tea_pro_name[$i] ?>
                                </a>
                            </h5>
                            <p class="card-text"><?= $strings["priority"] ?>: <?= $priority[$listProjects->tea_pro_priority[$i]] ?></p>
                            <p class="card-text"><?= $strings["status"] ?>: <?= $status[$listProjects->tea_pro_status[$i]] ?></p>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning"><?= $strings["no_items"] ?></div>
    <?php endif; ?>

    <?php
} else {
    // Show project details if a project is selected
    if (file_exists("../logos_clients/" . $clientDetail->org_id[0] . "." . $clientDetail->org_extension_logo[0])) {
        echo '<img src="../logos_clients/' . $clientDetail->org_id[0] . '.' . $clientDetail->org_extension_logo[0] . '" class="img-fluid mb-3"><br>';
    }

    ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title"><?= $projectDetail->pro_name[0] ?></h5>
            <p class="card-text"><?= nl2br($projectDetail->pro_description[0]) ?></p>
            <p><?= $strings["status"] ?>: <?= $status[$projectDetail->pro_status[0]] ?></p>
            <p><?= $strings["priority"] ?>: <?= $priority[$projectDetail->pro_priority[0]] ?></p>
            <?php if ($projectDetail->pro_phase_set[0] != "0"): ?>
                <p><?= $strings["current_phase"] ?>:
                    <?php
                    $tmpquery = "WHERE pha.project_id = '" . $projectDetail->pro_id[0] . "' AND status = '1'";
                    $currentPhase = new request();
                    $currentPhase->openPhases($tmpquery);
                    $comptCurrentPhase = count($currentPhase->pha_id);
                    if ($comptCurrentPhase == 0) echo $strings["no_current_phase"];
                    else {
                        for ($i=0;$i<$comptCurrentPhase;$i++) {
                            echo ($i+1) . '. ' . $currentPhase->pha_name[$i] . ' ';
                        }
                    }
                    ?>
                </p>
            <?php endif; ?>
            <p><?= $strings["url_dev"] ?>: <a href="<?= $projectDetail->pro_url_dev[0] ?>" target="_blank"><?= $projectDetail->pro_url_dev[0] ?></a></p>
            <p><?= $strings["url_prod"] ?>: <a href="<?= $projectDetail->pro_url_prod[0] ?>" target="_blank"><?= $projectDetail->pro_url_prod[0] ?></a></p>
            <p><?= $strings["created"] ?>: <?= createDate($projectDetail->pro_created[0], $_SESSION['timezoneSession']) ?></p>
            <p><?= $strings["modified"] ?>: <?= createDate($projectDetail->pro_modified[0], $_SESSION['timezoneSession']) ?></p>
        </div>
    </div>

    <?php
    $tmpquery = "WHERE tea.project = '" . $_SESSION['projectSession'] . "' AND tea.member = '" . $projectDetail->pro_owner[0] . "'";
    $detailContact = new request();
    $detailContact->openTeams($tmpquery);

    if ($detailContact->tea_published[0] == "0" && $detailContact->tea_project[0] == $_SESSION['projectSession']) {
        echo "<div class='alert alert-info'>" . $strings["contact_projectsite"] . ": <a href='contactdetail.php?id=" . $projectDetail->pro_owner[0] . "'>" . $projectDetail->pro_mem_name[0] . "</a></div>";
    }
}

require_once("include_footer.php");
?>
