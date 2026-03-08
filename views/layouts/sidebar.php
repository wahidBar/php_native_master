<?php $menus = get_user_menus(); ?>

<nav class="nxl-navigation">
    <div class="navbar-wrapper">

        <div class="m-header">
            <a href="?action=dashboard" class="b-brand">
                <img src="assets/images/logo-full.png" class="logo logo-lg" />
                <img src="assets/images/logo-abbr.png" class="logo logo-sm" />
            </a>
        </div>

        <div class="navbar-content">

            <!-- Dashboard Static -->
            <!-- <ul class="nxl-navbar">
                <li class="nxl-item">
                    <a href="?action=dashboard"
                       class="nxl-link <?= active_menu('dashboard') ?>">
                        <span class="nxl-micon">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>
            </ul> -->

            <!-- Dynamic Menu -->
            <?php render_menu($menus); ?>

        </div>
    </div>
</nav>
