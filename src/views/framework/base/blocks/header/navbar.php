<nav class="top-bar" data-topbar>
    <ul class="title-area">
        <li class="name">
            <h1><a href="<?php echo $helperUrl->getBaseUrl(); ?>"><?php echo $sitename; ?></a></h1>
        </li>
        <li class="toggle-topbar menu-icon">
            <a href="#">Menu</a>
        </li>
    </ul>

    <section class="top-bar-section">
        <ul class="left">
            <?php echo $helperNavbarLeft->renderMenuItems(); ?>
        </ul>

        <ul class="right">
            <?php echo $helperNavbarRight->renderMenuItems(); ?>
            <!--li class="has-dropdown">
                <a href="#">Right Button Dropdown</a>
                <ul class="dropdown">
                    <li><a href="#">First link in dropdown</a></li>
                </ul>
            </li-->
        </ul>
    </section>
</nav>

