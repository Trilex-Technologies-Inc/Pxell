<?php
// $Revision: 1.7 $

$changeProject = isset($changeProject) ? $changeProject : "";
$bouton = isset($bouton) && is_array($bouton) ? $bouton : array();

function projectSiteHeaderHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}

//--- validate user-status -----------
if ($_SESSION['projectSession'] != "" && $changeProject != "true") {
    $tmpquery = "WHERE pro.id = '" . $_SESSION['projectSession'] . "'";
    $projectDetail = new request();
    $projectDetail->openProjects($tmpquery);

    $tmpquery = "WHERE tea.project = '" . $_SESSION['projectSession'] . "' AND tea.member = '" . $_SESSION['idSession'] . "'";
    $memberTest = new request();
    $memberTest->openTeams($tmpquery);
    $teamMember = isset($memberTest->tea_id) && count($memberTest->tea_id) > 0 ? true : false;

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
    <meta charset="<?php echo projectSiteHeaderHtml($setCharset); ?>">
    <meta name="robots" content="none">
    <meta name="description" content="<?php echo projectSiteHeaderHtml($setDescription); ?>">
    <meta name="keywords" content="<?php echo projectSiteHeaderHtml($setKeywords); ?>">
    <title>
        <?php echo projectSiteHeaderHtml($setTitle); ?>
        <?php
        if ($_SESSION['projectSession'] != "" && $changeProject != "true") {
            echo " - " . projectSiteHeaderHtml($projectDetail->pro_name[0]);
        } else {
            echo " - " . projectSiteHeaderHtml($strings["my_projects"]);
        }
        ?>
    </title>

    <!-- Bootstrap (served locally) -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="../themes/<?php echo projectSiteHeaderHtml(THEME); ?>/calendar.css">
    <style>
        body.projectsite-shell {
            background: #f5f7fa;
            color: #162033;
        }

        .projectsite-topbar {
            align-items: center;
            background: #5f6d77;
            border-bottom: 1px solid rgba(255, 255, 255, 0.16);
            color: #ffffff;
            display: flex;
            min-height: 64px;
            padding: 14px 24px;
        }

        .projectsite-topbar__logo {
            display: block;
            height: 36px;
            margin-right: 14px;
            object-fit: contain;
            width: auto;
        }

        .projectsite-topbar h1 {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.3;
            margin: 0;
            overflow-wrap: anywhere;
        }

        .projectsite-layout {
            display: grid;
            grid-template-columns: 248px minmax(0, 1fr);
            min-height: calc(100vh - 64px);
        }

        .projectsite-sidebar {
            background: #ffffff;
            border-right: 1px solid #d9e3ec;
            padding: 18px 16px;
        }

        .projectsite-project {
            background: #f7fafc;
            border: 1px solid #d9e3ec;
            border-radius: 8px;
            margin-bottom: 14px;
            padding: 12px;
        }

        .projectsite-project__label {
            color: #657487;
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .projectsite-project__name {
            color: #162033;
            display: block;
            font-size: 0.98rem;
            font-weight: 750;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .projectsite-nav {
            display: grid;
            gap: 4px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .projectsite-nav a {
            align-items: center;
            border-radius: 8px;
            color: #44566c;
            display: flex;
            font-weight: 650;
            min-height: 38px;
            padding: 9px 11px;
            text-decoration: none;
            transition: background-color 0.16s ease, color 0.16s ease;
        }

        .projectsite-nav a:hover {
            background: #eef5fb;
            color: #164773;
        }

        .projectsite-nav a.active {
            background: #164773;
            color: #ffffff;
        }

        .projectsite-nav a.projectsite-nav__danger {
            color: #b42318;
        }

        .projectsite-nav a.projectsite-nav__danger:hover {
            background: #fff0ee;
            color: #8f1d14;
        }

        .projectsite-sidebar__actions {
            border-top: 1px solid #d9e3ec;
            display: grid;
            gap: 8px;
            margin-top: 16px;
            padding-top: 14px;
        }

        .projectsite-sidebar__actions .btn {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            font-weight: 650;
            justify-content: center;
            min-height: 38px;
        }

        .projectsite-main {
            min-width: 0;
            padding: 24px;
        }

        @media (max-width: 900px) {
            .projectsite-layout {
                grid-template-columns: 1fr;
            }

            .projectsite-sidebar {
                border-bottom: 1px solid #d9e3ec;
                border-right: 0;
            }

            .projectsite-nav {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .projectsite-main {
                padding: 18px;
            }
        }
    </style>
</head>
<body class="projectsite-shell">
<div class="projectsite-topbar">
    <img class="projectsite-topbar__logo" src="<?php echo projectSiteHeaderHtml($base_uri . 'themes/deepblue/images/logo-sidebar.png'); ?>" alt="TaskVibe">
    <h1><?php echo projectSiteHeaderHtml($titlePage); ?></h1>
</div>
<div class="projectsite-layout">
    <aside class="projectsite-sidebar">
        <?php if ($_SESSION['projectSession'] != "" && $changeProject != "true"): ?>
            <div class="projectsite-project">
                <span class="projectsite-project__label"><?php echo projectSiteHeaderHtml($strings["project"]); ?></span>
                <span class="projectsite-project__name"><?php echo projectSiteHeaderHtml($projectDetail->pro_name[0]); ?></span>
            </div>
            <ul class="projectsite-nav">
                <li><a class="<?php echo (isset($bouton[0]) && $bouton[0] == 'over') ? 'active' : ''; ?>" href="home.php"><?php echo $strings["home"]; ?></a></li>
                <li><a class="<?php echo (isset($bouton[1]) && $bouton[1] == 'over') ? 'active' : ''; ?>" href="showallcontacts.php"><?php echo $strings["project_team"]; ?></a></li>
                <li><a class="<?php echo (isset($bouton[2]) && $bouton[2] == 'over') ? 'active' : ''; ?>" href="showallteamtasks.php"><?php echo $strings["team_tasks"]; ?></a></li>
                <?php if ($projectDetail->pro_organization[0] != "" && $projectDetail->pro_organization[0] != "1"): ?>
                    <li><a class="<?php echo (isset($bouton[3]) && $bouton[3] == 'over') ? 'active' : ''; ?>" href="showallclienttasks.php"><?php echo $strings["client_tasks"]; ?></a></li>
                <?php endif; ?>
                <?php if ($fileManagement == "true"): ?>
                    <li><a class="<?php echo (isset($bouton[4]) && $bouton[4] == 'over') ? 'active' : ''; ?>" href="doclists.php"><?php echo $strings["document_list"]; ?></a></li>
                <?php endif; ?>
                <li><a class="<?php echo (isset($bouton[5]) && $bouton[5] == 'over') ? 'active' : ''; ?>" href="showallthreadtopics.php"><?php echo $strings["bulletin_board"]; ?></a></li>
                <?php if ($enableHelpSupport == "true"): ?>
                    <li><a class="<?php echo (isset($bouton[6]) && $bouton[6] == 'over') ? 'active' : ''; ?>" href="showallsupport.php?project=<?php echo projectSiteHeaderHtml($_SESSION['projectSession']); ?>"><?php echo $strings["support"]; ?></a></li>
                <?php endif; ?>
                <li><a class="projectsite-nav__danger" href="../general/login.php?logout=true"><?php echo $strings["logout"]; ?></a></li>
            </ul>
        <?php endif; ?>

        <div class="projectsite-sidebar__actions">
            <a class="btn btn-outline-primary" href="home.php?changeProject=true"><?php echo $strings["my_projects"]; ?></a>
            <a class="btn btn-outline-secondary" href="changepassword.php"><?php echo $strings["preferences"]; ?></a>
        </div>
    </aside>

    <main class="projectsite-main">
