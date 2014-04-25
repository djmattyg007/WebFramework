<?php

namespace MattyG\Framework\Helper\View\Menu;

use \MattyG\Framework\Helper\View\Menu as Menu;

class Navbar extends Menu
{
    const CLASS_HASCHILDREN = "has-dropdown";
    const CLASS_DROPDOWN    = "dropdown";

    /**
     * @param array $menuItems
     * @param bool $separator This is hard-coded to false for sub-menus.
     * @return string
     */
    public function renderMenuItems($menuItems = null, $separator = null)
    {
        if ($menuItems === null) {
            $menuItems = $this->getMenuItems();
        }
        if ($separator === null) {
            $separator = $this->getMenuSetting("separator");
        }

        $items = array();
        foreach ($menuItems as $menuItem) {
            if (isset($menuItem["divider"]) && $menuItem["divider"] === true) {
                $items[] = '<li class="divider"></li>';
                continue;
            }

            $classes = array();
            if ($this->isActiveRoute($menuItem["route"])) {
                $classes[] = parent::CLASS_ACTIVE;
            }
            if (!empty($menuItem["children"])) {
                $classes[] = self::CLASS_HASCHILDREN;
            }
            if (isset($menuItem["classes"])) {
                $classes = array_merge($classes, $menuItem["classes"]);
            }

            $tag = '<li';
            if ($classAttribute = implode(" ", $classes)) {
                $tag .= ' class="' . $classAttribute . '">';
            } else {
                $tag .= '>';
            }

            if (isset($menuItem["route"])) {
                $tag .= '<a href="' . $this->urlHelper->getRouteUrl($menuItem["route"]) . '">';
            } else {
                $tag .= '<label>';
            }
            $tag .= $menuItem["label"];
            if (isset($menuItem["route"])) {
                $tag .= '</a>';
            } else {
                $tag .= '</label>';
            }

            if (!empty($menuItem["children"]) && !isAssoc($menuItem["children"])) {
                $tag .= '<ul class="' . self::CLASS_DROPDOWN . '">';
                $tag .= $this->renderMenuItems($menuItem["children"], false);
                $tag .= '</ul>';
            }

            $tag .= '</li>';

            $items[] = $tag;
        }

        return implode("", $items);
    }
}
