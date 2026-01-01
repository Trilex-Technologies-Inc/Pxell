<?php
$checkSession = true;
require_once("../includes/library.php");

if ($action == "delete") {
    $id = str_replace("**", ",", $id);
    $tmpquery1 = 'DELETE FROM ' . $tableCollab['meetings_time'] . " WHERE id IN($id)";
    connectSql($tmpquery1);

    if ($meeting != "") {
        header("Location: ../meetings/addmeetingtime.php?id=$meeting&msg=delete");
    } else {
        header("Location: ../general/home.php?msg=delete");
    }
    exit;
}

/* Meeting detail */
$tmpquery = "WHERE mee.id = '$meeting'";
$meetingDetail = new request();
$meetingDetail->openMeetings($tmpquery);

/* Project detail */
$tmpquery = "WHERE pro.id = '" . $meetingDetail->mee_project[0] . "'";
$projectDetail = new request();
$projectDetail->openProjects($tmpquery);

/* Permission check */
$tmpquery = "WHERE tea.project = '" . $meetingDetail->mee_project[0] . "' 
             AND tea.member = '" . $_SESSION['idSession'] . "'";
$memberTest = new request();
$memberTest->openTeams($tmpquery);

if (count($memberTest->tea_id) == 0 && $projectsFilter == "true") {
    header("Location: ../general/permissiondenied.php");
    exit;
}

/* Breadcrumbs */
$breadcrumbs[] = buildLink("../projects/listprojects.php?", $strings["projects"], LINK_INSIDE);
$breadcrumbs[] = buildLink("../projects/viewproject.php?id=" . $projectDetail->pro_id[0], $projectDetail->pro_name[0], LINK_INSIDE);
$breadcrumbs[] = buildLink("../meetings/listmeetings.php?project=" . $projectDetail->pro_id[0], $strings["meetings"], LINK_INSIDE);
$breadcrumbs[] = buildLink("../meetings/viewmeeting.php?id=" . $meetingDetail->mee_id[0], $meetingDetail->mee_name[0], LINK_INSIDE);
$breadcrumbs[] = $strings["delete_meeting_time"];

require_once("../themes/" . THEME . "/header.php");

/* Data */
$id = str_replace("**", ",", $id);
$tmpquery = "WHERE mti.id IN($id) ORDER BY mti.id";
$listMeetingTime = new request();
$listMeetingTime->openMeetingTime($tmpquery);
?>

<div class="container mt-4">
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><?= $strings["delete_meeting_time"]; ?></h5>
        </div>

        <div class="card-body">
            <p class="fw-bold text-danger">
                <?= $strings["delete_following"]; ?>
            </p>

            <ul class="list-group mb-4">
                <?php foreach ($listMeetingTime->mti_id as $i => $timeId): ?>
                    <li class="list-group-item">
                        <strong>#<?= $timeId ?></strong><br>
                        <?= $strings['worked_hours']; ?>:
                        <?= $listMeetingTime->mti_hours[$i]; ?><br>
                        <small class="text-muted">
                            <?= htmlspecialchars($listMeetingTime->mti_comments[$i]); ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>

            <form method="post"
                  action="../meetings/deletemeetingtime.php?meeting=<?= $meeting ?>&action=delete&id=<?= $id ?>">
                <div class="d-flex gap-2">
                    <button type="submit" name="delete" class="btn btn-danger">
                        <?= $strings['delete']; ?>
                    </button>

                    <button type="button"
                            class="btn btn-secondary"
                            onclick="history.back();">
                        <?= $strings['cancel']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once("../themes/" . THEME . "/footer.php");
?>
