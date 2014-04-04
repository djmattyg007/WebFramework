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
            <?php foreach ($helperNavbarLeft->getMenuItems() as $menuItem): ?>
            <?php if ($helperNavbarLeft->isActiveRoute($menuItem["route"])): ?>
            <li class="active">
            <?php else: ?>
            <li>
            <?php endif; ?>
                <a href="<?php echo $helperUrl->getRouteUrl($menuItem["route"]); ?>"><?php echo $helperTranslate->__($menuItem["label"]); ?></a>
            </li>
            <?php endforeach; ?>
        </ul>

        <ul class="right">
            <li class="active"><a href="#">Right Button Active</a></li>
            <?php foreach ($helperNavbarRight->getMenuItems() as $menuItem): ?>
            <?php if ($helperNavbarLeft->isActiveRoute($menuItem["route"])): ?>
            <li class="active">
            <?php else: ?>
            <li>
            <?php endif; ?>
                <a href="<?php echo $helperUrl->getRouteUrl($menuItem["route"]); ?>"><?php echo $helperTranslate->__($menuItem["label"]); ?></a>
            </li>
            <?php endforeach; ?>
            <!--li class="has-dropdown">
                <a href="#">Right Button Dropdown</a>
                <ul class="dropdown">
                    <li><a href="#">First link in dropdown</a></li>
                </ul>
            </li-->
        </ul>
    </section>
</nav>

