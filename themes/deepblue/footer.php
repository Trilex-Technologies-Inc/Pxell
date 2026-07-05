<?php // $Revision: 1.1 $
/* vim: set expandtab ts=4 sw=4 sts=4: */

/**
 * $Id: footer.php,v 1.1 2004/11/09 17:13:48 pixtur Exp $
 * 
 * Copyright (c) 2003 by the NetOffice developers
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

echo '<footer id="footer" class="site-footer">';
echo '<div class="site-footer__main">';
echo '<span class="site-footer__brand">Powered by <strong>Taskvibe</strong></span>';
echo '<a class="site-footer__link" href="http://www.taskvibe.net" target="_blank">www.taskvibe.net</a>';
echo '<span class="site-footer__chip">v' . htmlspecialchars($version) . '</span>';

if ($notLogged != true && $blank != true) {
    echo '<span class="site-footer__chip">Connected users: ' . htmlspecialchars($connectedUsers) . '</span>';
}

if ($footerDev == true) {
    $parse_end = getmicrotime();
    $parse = $parse_end - $parse_start;
    $parse = round($parse, 3);
    echo '<span class="site-footer__chip">' . htmlspecialchars($parse) . ' secondes</span>';
    echo '<span class="site-footer__chip">databaseType ' . htmlspecialchars($databaseType) . '</span>';
    echo '<span class="site-footer__chip">select requests ' . htmlspecialchars($comptRequest) . '</span>';
    echo '<a class="site-footer__link" href="http://validator.w3.org/check/referer" target="w3c">w3c</a>';
}

echo '</div>';
echo '</footer>'

?>

<!-- JavaScript -->
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const content = document.querySelector('.content');

    // Toggle sidebar
    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }

    // Mobile menu toggle
    if (sidebar && mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 992 &&
            sidebar &&
            mobileToggle &&
            !sidebar.contains(event.target) &&
            !mobileToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Adjust content margin on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992 && sidebar && content) {
            if (sidebar.classList.contains('collapsed')) {
                content.style.marginLeft = 'calc(var(--sidebar-collapsed-width) + var(--content-padding))';
            } else {
                content.style.marginLeft = 'calc(var(--sidebar-width) + var(--content-padding))';
            }
        }
    });
</script>
</body>

</html>