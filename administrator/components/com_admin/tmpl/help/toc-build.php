<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// Include the $menu as a php array.
include __DIR__ . '/toc-src.php';
// phpcs:enable PSR1.Files.SideEffects

// Initialise variables used the Help men.
$tocid = 1000;
$toclevel = 1;
$liid = 0;
$firstpass = true;

// Compose the menu.
$toc = buildMenu($menu, $tocid, $toclevel, $liid, $firstpass);

function buildMenu(array &$items, int &$tocid, int &$toclevel, int &$liid, bool $firstpass = false): string
{
    // don't set a ul on the first pass
    if ($firstpass) {
        $html = "";
    } else {
        $tocid += 1;
        // Increase the toclevel on entry
        $toclevel += 1;
        if ($toclevel > 1) {
            $collapse = ' mm-collapse';
        } else {
            $collapse = '';
        }
        $html = "<ul id=\"collapse{$tocid}\" class=\"collapse-level-1{$collapse}\">\n";
    }
    foreach ($items as $label => $value) {
        $text = Text::_('COM_ADMIN_HELP_' . $label);
        $lclabel = strtolower(str_replace('_', '-', $label));
        $liid += 1;

        if (is_array($value)) {
            // This is a folder list item
            $icon = "<span class=\"icon-folder icon-fw\" aria-hidden=\"true\"></span>";
            $wrap_label = "<span class=\"item-title\">{$text}</span>";
            $html .= "<li id=\"li{$liid}\" class=\"item parent item-level-{$toclevel}\">";
            $html .= "<a href=\"#\" class=\"has-arrow\">";
            $html .= "{$icon}{$wrap_label}</a>\n";
            if (!empty($value)) {
                // Recursively build sublist.
                $html .= buildMenu($value, $tocid, $toclevel, $liid);
            }
            $html .= "</li>\n";
        } else {
            // This is an article list item.
            $icon = "<span class=\"icon-file-alt icon-fw\" aria-hidden=\"true\"></span>";
            // The url is help.joomla.org + $label (the help key).
            $url = Help::createUrl($label);
            $link = "<a data-id=\"{$lclabel}\" href=\"{$url}\" target=\"helpFrame\">{$icon}{$text}</a>\n";
            $html .= "<li class=\"item item-level-{$toclevel}\">{$link}</li>\n";
        }
    }
    // don't set a ul on the first pass
    if (!$firstpass) {
        $html .= "</ul>\n";
    }
    // On return decrease the toclevel
    $toclevel -= 1;
    return $html;
}
