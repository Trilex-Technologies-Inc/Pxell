<?php
// $Revision: 1.14 $
/**
 * header.php — Bootstrap sidebar version (PHP 5 compatible)
 */

echo $setDoctype . "\n";
echo $setCopyright . "\n";
?>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $setCharset; ?>">
        <title><?php echo htmlspecialchars($setTitle); ?></title>
        <meta name="robots" content="none">
        <meta name="description" content="<?php echo htmlspecialchars($setDescription); ?>">
        <meta name="keywords" content="<?php echo htmlspecialchars($setKeywords); ?>">

        <!-- JavaScript files -->
        <script type="text/javascript" src="../javascript/general.js"></script>
        <script type="text/javascript" src="../javascript/overlib/overlib.js"></script>
        <script type="text/javascript" src="../javascript/jscalendar/calendar.js"></script>
        <script type="text/javascript" src="../javascript/jscalendar/lang/calendar-en.js"></script>
        <script type="text/javascript" src="../javascript/jscalendar/calendar-setup.js"></script>

        <!-- CSS files -->
        <link rel="stylesheet" href="../themes/default/stylesheet.css" type="text/css">
        <link rel="stylesheet" href="../themes/default/calendar/theme.css" type="text/css">

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <style>
            :root {
                --primary-color: #0d6efd;
                --sidebar-width: 250px;
                --sidebar-collapsed-width: 70px;
                --content-padding: 20px;
                --transition-speed: 0.3s;
            }

            body {
                background-color: #f8f9fa;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                overflow-x: hidden;
            }

            /* Sidebar Styles */
            .sidebar {
                width: var(--sidebar-width);
                height: 100vh;
                background-color: #fff;
                border-right: 1px solid #dee2e6;
                position: fixed;
                left: 0;
                top: 0;
                padding: 15px 0;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
                z-index: 1000;
                transition: width var(--transition-speed);
                display: flex;
                flex-direction: column;
            }

            .sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            .sidebar-content {
                flex: 1;
                overflow-y: auto;
                padding: 0 15px;
            }

            .sidebar .logo {
                text-align: center;
                margin-bottom: 25px;
                padding: 0 15px 15px;
                border-bottom: 1px solid #eee;
                transition: all var(--transition-speed);
            }

            .sidebar.collapsed .logo {
                padding: 0 5px 15px;
            }

            .sidebar .logo img {
                max-height: 60px;
                transition: all var(--transition-speed);
            }

            .sidebar.collapsed .logo img {
                max-height: 40px;
            }

            .sidebar .logo-text {
                font-weight: bold;
                color: var(--primary-color);
                margin-top: 10px;
                transition: opacity var(--transition-speed);
            }

            .sidebar.collapsed .logo-text {
                opacity: 0;
                display: none;
            }

            .sidebar .nav-link {
                color: #495057;
                font-weight: 500;
                padding: 12px 15px;
                margin-bottom: 5px;
                border-radius: 5px;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                white-space: nowrap;
            }

            .sidebar.collapsed .nav-link {
                padding: 12px 10px;
                justify-content: center;
            }

            .sidebar .nav-link:hover {
                background-color: #e9ecef;
                color: var(--primary-color);
            }

            .sidebar .nav-link.active {
                background-color: var(--primary-color);
                color: white;
            }

            .sidebar .nav-link i {
                width: 20px;
                text-align: center;
                margin-right: 10px;
                transition: margin var(--transition-speed);
            }

            .sidebar.collapsed .nav-link i {
                margin-right: 0;
            }

            .sidebar .nav-text {
                transition: opacity var(--transition-speed);
            }

            .sidebar.collapsed .nav-text {
                opacity: 0;
                display: none;
            }

            .user-info {
                border-top: 1px solid #dee2e6;
                margin-top: 20px;
                padding: 20px 15px 0;
                font-size: 0.9rem;
                transition: padding var(--transition-speed);
            }

            .sidebar.collapsed .user-info {
                padding: 20px 10px 0;
            }

            .user-info a {
                color: #6c757d;
                text-decoration: none;
                display: flex;
                align-items: center;
                padding: 8px 0;
                transition: color 0.2s;
                white-space: nowrap;
            }

            .sidebar.collapsed .user-info a {
                justify-content: center;
            }

            .user-info a:hover {
                color: var(--primary-color);
            }

            .user-info-text {
                transition: opacity var(--transition-speed);
            }

            .sidebar.collapsed .user-info-text {
                opacity: 0;
                display: none;
            }

            /* Toggle Button */
            .sidebar-toggle {
                position: absolute;
                top: 15px;
                right: -15px;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
                z-index: 1001;
                transition: transform var(--transition-speed);
            }

            .sidebar-toggle:hover {
                transform: scale(1.1);
            }

            .sidebar.collapsed .sidebar-toggle i {
                transform: rotate(180deg);
            }

            /* Main Content */
            .content {
                margin-left: calc(var(--sidebar-width) + var(--content-padding));
                padding: var(--content-padding);
                min-height: 100vh;
                transition: margin-left var(--transition-speed);
            }

            .sidebar.collapsed ~ .content {
                margin-left: calc(var(--sidebar-collapsed-width) + var(--content-padding));
            }
   /* Admin Cards */
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 5px;
        }

        .admin-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e9ecef;
        }

        .admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .admin-card h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .admin-card p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .admin-card a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }

        .admin-card a i {
            margin-left: 5px;
            transition: transform 0.2s;
        }

        .admin-card a:hover i {
            transform: translateX(3px);
        }

        .warning-card {
            border-left: 4px solid #dc3545;
        }

        .warning-card h3 {
            color: #dc3545;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .content {
                margin-left: 0 !important;
            }

            .mobile-menu-toggle {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1001;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 5px;
                padding: 8px 12px;
            }
        }

        @media (min-width: 993px) {
            .mobile-menu-toggle {
                display: none;
            }
        }

        /* Scrollbar styling for sidebar */
        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>

        <?php echo $headBonus; ?>
    </head>
<body>

    <!-- Sidebar -->
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="sidebar-content">
            <div class="logo">
                <?php
                //--- Client logo ---
                if (!$blank && $version >= "2.0") {
                    $tmpquery = "WHERE org.id = '1'";
                    $clientHeader = new request();
                    $clientHeader->openOrganizations($tmpquery);
                }

                $logoFile = "../logos_clients/1." . @$clientHeader->org_extension_logo[0];
                if (!$blank && file_exists($logoFile)) {
                    echo '<img src="' . $logoFile . '" alt="' . htmlspecialchars($clientHeader->org_name[0]) . '">';
                } else {
                    echo '<img src="../themes/deepblue/img/logo_netoffice.gif" alt="NetOffice">';
                }
                ?>
            </div>

            <!-- Navigation -->
            <nav class="nav flex-column">
                <?php
                $sections_shown = array();
                $blockHeader = new block();

                if (!defined("INSTALL")) {
                    if ($notLogged) {
                        $sections_shown = array('login', 'requirements', 'license');
                    } else {
                        $sections_shown = array('home', 'projects', 'clients', 'reports', 'search', 'calendar', 'bookmarks', 'preferences');
                        if ($_SESSION['profilSession'] == "0") {
                            $sections_shown[] = 'admin';
                        }
                    }
                }

                foreach ($sections_shown as $nth_section) {
                    $url = isset($headerSections[$nth_section]) ? $headerSections[$nth_section] : '#';
                    $label = isset($strings[$nth_section]) ? $strings[$nth_section] : ucfirst($nth_section);
                    $active = ($nth_section == $pageSection) ? 'active' : '';

                    // Set icons
                    switch ($nth_section) {
                        case 'home':
                            $icon = 'fa-house';
                            break;
                        case 'projects':
                            $icon = 'fa-briefcase';
                            break;
                        case 'clients':
                            $icon = 'fa-users';
                            break;
                        case 'reports':
                            $icon = 'fa-chart-line';
                            break;
                        case 'search':
                            $icon = 'fa-magnifying-glass';
                            break;
                        case 'calendar':
                            $icon = 'fa-calendar';
                            break;
                        case 'bookmarks':
                            $icon = 'fa-bookmark';
                            break;
                        case 'preferences':
                            $icon = 'fa-gear';
                            break;
                        case 'admin':
                            $icon = 'fa-user-shield';
                            break;
                        case 'login':
                            $icon = 'fa-right-to-bracket';
                            break;
                        case 'requirements':
                            $icon = 'fa-list-check';
                            break;
                        case 'license':
                            $icon = 'fa-file-contract';
                            break;
                        default:
                            $icon = 'fa-circle';
                            break;
                    }

                    echo '<a class="nav-link ' . $active . '" href="' . $url . '"><i class="fa ' . $icon . ' me-2"></i>' . $label . '</a>';
                }
                ?>
            </nav>

            <!-- User Info -->
            <div class="user-info ">
                <?php if (!$blank && !$notLogged): ?>
                    <div><strong><?php echo htmlspecialchars($_SESSION['nameSession']); ?></strong></div>
                    <a href="../general/login.php?logout=true" class="d-block"><i
                                class="fa fa-lock me-1"></i><?php echo $strings["logout"]; ?></a>
                    <a href="../projects_site/home.php?changeProject=true" class="d-block"><i
                                class="fa fa-house me-1"></i><?php echo $strings["go_projects_site"]; ?></a>
                <?php else: ?>
                    <a href="../general/login.php" class="btn btn-primary btn-sm w-100"><i
                                class="fa fa-right-to-bracket me-1"></i>Login</a>
                <?php endif; ?>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="content">

<?php
//--- Breadcrumbs ---
if (!empty($breadcrumbs)) {
    echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    foreach ($breadcrumbs as $crump) {
        echo '<li class="breadcrumb-item">' . $crump . '</li>';
    }
    echo '</ol></nav>';
}

//--- Messages ---
if (!empty($msg)) {
    require_once('../includes/messages.php');
    $template->messagebox($msgLabel);
}

//--- Page Title ---
if (!empty($pageTitle)) {
    echo '<div class="d-flex justify-content-between align-items-center mb-4">';
    echo '<h2 class="h3 mb-0">' . ($pageTitle) . '</h2>';
    echo '</div>';
}
?>