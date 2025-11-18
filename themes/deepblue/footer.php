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

echo "<p id='footer'>Powered by Pxell http://www.pxell.com v$version";

if ($notLogged != true && $blank != true) {
    echo ' - Connected users: ' . $connectedUsers;
} 

if ($footerDev == true) {
    $parse_end = getmicrotime();
    $parse = $parse_end - $parse_start;
    $parse = round($parse, 3);
    echo " - $parse secondes - databaseType $databaseType - select requests $comptRequest";
    echo ' - <a href="http://validator.w3.org/check/referer" target="w3c">w3c</a> (in progress)';
} 

echo '</p>'

?>

<!-- JavaScript -->
<script>
    // Toggle sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

    // Mobile menu toggle
    document.getElementById('mobileMenuToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.getElementById('mobileMenuToggle');

        if (window.innerWidth <= 992 &&
            !sidebar.contains(event.target) &&
            !mobileToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    // Adjust content margin on window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content');

        if (window.innerWidth > 992) {
            if (sidebar.classList.contains('collapsed')) {
                content.style.marginLeft = 'calc(70px + 20px)';
            } else {
                content.style.marginLeft = 'calc(250px + 20px)';
            }
        }
    });
</script>
</body>
</html>
