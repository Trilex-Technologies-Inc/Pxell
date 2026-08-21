<?php
$checkSession = true;
require_once("../includes/library.php");

$updateProject = isset($updateProject) ? $updateProject : "";
$changeProject = isset($changeProject) ? $changeProject : "";
$project = isset($project) ? $project : "";
$bouton = array();

if ($project != "" && !is_numeric($project)) {
    $project = "";
}

function projectSiteHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}

function projectSiteLabel($labels, $key)
{
    return isset($labels[$key]) ? $labels[$key] : $key;
}

if ($updateProject == "true") {
    $tmpquery = "WHERE tea.member = '" . $_SESSION['idSession'] . "' AND pro.id = '$project' AND pro.status IN(0,2,3) AND pro.published = '0'";
    $testProject = new request();
    $testProject->openTeams($tmpquery);
    $comptTestProject = isset($testProject->tea_id) ? count($testProject->tea_id) : 0;

    if ($comptTestProject != "0") {
        $_SESSION['projectSession'] = $project;
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

?>
<style>
    .projectsite-page {
        display: grid;
        gap: 18px;
    }

    .projectsite-hero {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 12px 32px rgba(33, 46, 69, 0.08);
        padding: 22px;
    }

    .projectsite-hero__eyebrow {
        color: #2f6f6a;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .projectsite-hero h1 {
        color: #162033;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .projectsite-hero p {
        color: #657487;
        margin: 7px 0 0;
        max-width: 720px;
    }

    .projectsite-count {
        align-items: center;
        background: #eef6f4;
        border: 1px solid #c9e3df;
        border-radius: 8px;
        color: #245f5b;
        display: inline-flex;
        font-weight: 700;
        min-height: 40px;
        padding: 8px 12px;
        white-space: nowrap;
    }

    .project-grid {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    }

    .project-card {
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(33, 46, 69, 0.06);
        color: inherit;
        display: grid;
        gap: 14px;
        min-height: 184px;
        padding: 18px;
        text-decoration: none;
        transition: border-color 0.16s ease, box-shadow 0.16s ease, transform 0.16s ease;
    }

    .project-card:hover {
        border-color: #8fbfba;
        box-shadow: 0 14px 34px rgba(33, 46, 69, 0.12);
        color: inherit;
        text-decoration: none;
        transform: translateY(-2px);
    }

    .project-card__top {
        display: flex;
        gap: 12px;
        justify-content: space-between;
    }

    .project-card h2 {
        color: #162033;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: 0;
        line-height: 1.25;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .project-card__client {
        color: #657487;
        font-size: 0.88rem;
        margin: 6px 0 0;
        overflow-wrap: anywhere;
    }

    .project-card__badge {
        align-items: center;
        background: #f4f7fa;
        border: 1px solid #d9e3ec;
        border-radius: 999px;
        color: #526174;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 0.78rem;
        font-weight: 700;
        min-height: 28px;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .project-card__meta {
        display: grid;
        gap: 8px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .project-meta {
        background: #f7fafc;
        border: 1px solid #e2e9ef;
        border-radius: 8px;
        padding: 10px;
    }

    .project-meta span {
        color: #657487;
        display: block;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .project-meta strong {
        color: #162033;
        display: block;
        font-size: 0.92rem;
        font-weight: 700;
        margin-top: 3px;
        overflow-wrap: anywhere;
    }

    .project-card__owner {
        color: #526174;
        font-size: 0.88rem;
        overflow-wrap: anywhere;
    }

    .project-empty,
    .project-overview {
        background: #ffffff;
        border: 1px solid #d9e3ec;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(33, 46, 69, 0.06);
        padding: 22px;
    }

    .project-overview__header {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-start;
        justify-content: space-between;
        border-bottom: 1px solid #e2e9ef;
        margin-bottom: 18px;
        padding-bottom: 18px;
    }

    .project-logo {
        max-height: 72px;
        max-width: 180px;
        object-fit: contain;
    }

    .project-overview h1 {
        color: #162033;
        font-size: 1.55rem;
        font-weight: 700;
        letter-spacing: 0;
        margin: 0;
    }

    .project-description {
        color: #526174;
        margin: 8px 0 0;
        max-width: 780px;
    }

    .project-stats {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        margin-bottom: 18px;
    }

    .project-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    @media (max-width: 760px) {
        .projectsite-hero,
        .project-overview {
            padding: 18px;
        }

        .project-card__top,
        .project-overview__header {
            display: grid;
        }

        .project-card__meta {
            grid-template-columns: 1fr;
        }
    }
</style>
<?php

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
    $comptListProjects = isset($listProjects->tea_id) ? count($listProjects->tea_id) : 0;
    ?>

    <div class="projectsite-page">
        <section class="projectsite-hero">
            <div>
                <div class="projectsite-hero__eyebrow"><?php echo $strings["project_site"]; ?></div>
                <h1><?php echo $strings["my_projects"]; ?></h1>
                <p><?php echo $strings["welcome"] . " " . projectSiteHtml($_SESSION['nameSession']) . " " . $strings["your_projectsite"]; ?></p>
            </div>
            <div class="projectsite-count"><?php echo $comptListProjects . " " . $strings["project"]; ?></div>
        </section>

        <?php if ($comptListProjects != 0): ?>
            <div class="project-grid">
                <?php for ($i=0; $i<$comptListProjects; $i++): ?>
                    <a class="project-card" href="home.php?updateProject=true&amp;project=<?php echo projectSiteHtml($listProjects->tea_pro_id[$i]); ?>">
                        <div class="project-card__top">
                            <div>
                                <h2><?php echo projectSiteHtml($listProjects->tea_pro_name[$i]); ?></h2>
                                <?php if ($listProjects->tea_org2_name[$i] != ""): ?>
                                    <p class="project-card__client"><?php echo projectSiteHtml($listProjects->tea_org2_name[$i]); ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="project-card__badge"><?php echo projectSiteLabel($status, $listProjects->tea_pro_status[$i]); ?></span>
                        </div>
                        <div class="project-card__meta">
                            <div class="project-meta">
                                <span><?php echo $strings["priority"]; ?></span>
                                <strong><?php echo projectSiteLabel($priority, $listProjects->tea_pro_priority[$i]); ?></strong>
                            </div>
                            <div class="project-meta">
                                <span><?php echo $strings["owner"]; ?></span>
                                <strong><?php echo projectSiteHtml($listProjects->tea_mem2_login[$i]); ?></strong>
                            </div>
                        </div>
                        <?php if ($listProjects->tea_mem2_email_work[$i] != ""): ?>
                            <div class="project-card__owner"><?php echo projectSiteHtml($listProjects->tea_mem2_email_work[$i]); ?></div>
                        <?php endif; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <div class="project-empty">
                <h2 class="h5 mb-2"><?php echo $strings["no_items"]; ?></h2>
                <div class="text-muted"><?php echo $strings["my_projects"]; ?></div>
            </div>
        <?php endif; ?>
    </div>

    <?php
} else {
    // Show project details if a project is selected
    $logoPath = $base_uri . "themes/deepblue/img/logo-sidebar.png";
    $logoAlt = "TaskVibe";
    if (isset($clientDetail->org_id[0]) && $clientDetail->org_id[0] != "" && $clientDetail->org_extension_logo[0] != "") {
        $candidateLogo = "../logos_clients/" . $clientDetail->org_id[0] . "." . $clientDetail->org_extension_logo[0];
        if (file_exists($candidateLogo)) {
            $logoPath = $candidateLogo;
            $logoAlt = $clientDetail->org_name[0];
        }
    }

    ?>
    <div class="projectsite-page">
        <section class="project-overview">
            <div class="project-overview__header">
                <div>
                    <div class="projectsite-hero__eyebrow"><?php echo $strings["project_site"]; ?></div>
                    <h1><?php echo projectSiteHtml($projectDetail->pro_name[0]); ?></h1>
                    <?php if ($projectDetail->pro_description[0] != ""): ?>
                        <p class="project-description"><?php echo nl2br(projectSiteHtml($projectDetail->pro_description[0])); ?></p>
                    <?php endif; ?>
                </div>
                <img src="<?php echo projectSiteHtml($logoPath); ?>" class="project-logo" alt="<?php echo projectSiteHtml($logoAlt); ?>" onerror="this.onerror=null;this.src='<?php echo projectSiteHtml($base_uri . 'themes/deepblue/img/logo-sidebar.png'); ?>'">
            </div>

            <div class="project-stats">
                <div class="project-meta">
                    <span><?php echo $strings["status"]; ?></span>
                    <strong><?php echo projectSiteLabel($status, $projectDetail->pro_status[0]); ?></strong>
                </div>
                <div class="project-meta">
                    <span><?php echo $strings["priority"]; ?></span>
                    <strong><?php echo projectSiteLabel($priority, $projectDetail->pro_priority[0]); ?></strong>
                </div>
                <div class="project-meta">
                    <span><?php echo $strings["created"]; ?></span>
                    <strong><?php echo projectSiteHtml(createDate($projectDetail->pro_created[0], $_SESSION['timezoneSession'])); ?></strong>
                </div>
                <div class="project-meta">
                    <span><?php echo $strings["modified"]; ?></span>
                    <strong><?php echo projectSiteHtml(createDate($projectDetail->pro_modified[0], $_SESSION['timezoneSession'])); ?></strong>
                </div>
            </div>

            <?php if ($projectDetail->pro_phase_set[0] != "0"): ?>
                <div class="project-meta mb-3">
                    <span><?php echo $strings["current_phase"]; ?></span>
                    <strong>
                        <?php
                        $tmpquery = "WHERE pha.project_id = '" . $projectDetail->pro_id[0] . "' AND status = '1'";
                        $currentPhase = new request();
                        $currentPhase->openPhases($tmpquery);
                        $comptCurrentPhase = isset($currentPhase->pha_id) ? count($currentPhase->pha_id) : 0;
                        if ($comptCurrentPhase == 0) {
                            echo $strings["no_current_phase"];
                        } else {
                            for ($i=0;$i<$comptCurrentPhase;$i++) {
                                if ($i > 0) {
                                    echo ", ";
                                }
                                echo projectSiteHtml($currentPhase->pha_name[$i]);
                            }
                        }
                        ?>
                    </strong>
                </div>
            <?php endif; ?>

            <div class="project-links">
                <?php if ($projectDetail->pro_url_dev[0] != ""): ?>
                    <a class="btn btn-outline-primary" href="<?php echo projectSiteHtml($projectDetail->pro_url_dev[0]); ?>" target="_blank" rel="noopener noreferrer"><?php echo $strings["url_dev"]; ?></a>
                <?php endif; ?>
                <?php if ($projectDetail->pro_url_prod[0] != ""): ?>
                    <a class="btn btn-outline-primary" href="<?php echo projectSiteHtml($projectDetail->pro_url_prod[0]); ?>" target="_blank" rel="noopener noreferrer"><?php echo $strings["url_prod"]; ?></a>
                <?php endif; ?>
                <a class="btn btn-primary" href="home.php?changeProject=true"><?php echo $strings["my_projects"]; ?></a>
            </div>
        </section>

        <?php
        $tmpquery = "WHERE tea.project = '" . $_SESSION['projectSession'] . "' AND tea.member = '" . $projectDetail->pro_owner[0] . "'";
        $detailContact = new request();
        $detailContact->openTeams($tmpquery);

        $comptDetailContact = isset($detailContact->tea_id) ? count($detailContact->tea_id) : 0;
        if ($comptDetailContact != "0" && $detailContact->tea_published[0] == "0" && $detailContact->tea_project[0] == $_SESSION['projectSession']) {
            echo "<div class='alert alert-info mb-0'>" . $strings["contact_projectsite"] . ": <a href='contactdetail.php?id=" . projectSiteHtml($projectDetail->pro_owner[0]) . "'>" . projectSiteHtml($projectDetail->pro_mem_name[0]) . "</a></div>";
        }
        ?>
    </div>

<?php
}

require_once("include_footer.php");
?>
