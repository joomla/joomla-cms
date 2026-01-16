<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// Increase the toclevel on entry
$this->toclevel += 1;
if ($this->toclevel > 1) {
    $collapse = ' mm-collapse';
    echo "<ul class=\"collapse-level-1{$collapse}\">\n";
}
foreach ($menu as $label => $value) {
    $text = Text::_('COM_ADMIN_HELP_' . $label);
    $lclabel = strtolower(str_replace('_', '-', $label));

    if (is_array($value)) {
        // This is a folder list item
            $icon = "<span class=\"icon-folder\" aria-hidden=\"true\"></span>";
        $wrap_label = "<span class=\"item-title\">{$text}</span>";
        echo "<li class=\"item parent item-level-{$this->toclevel}\">";
        echo "<a href=\"#\" class=\"has-arrow\">";
        echo "{$icon}{$wrap_label}</a>\n";
        if (!empty($value)) {
            // Recursively build sublist.
            $this->renderSubmenu(JPATH_ADMINISTRATOR . '/components/com_admin/tmpl/help/toc-build.php', $value);
        }
        echo "</li>\n";
    } else {
        // This is an article list item.
        $icon = "<span class=\"icon-file-alt\" aria-hidden=\"true\"></span>";
        // The url is help.joomla.org + $label (the help key).
        $url = Help::createUrl($label);
        $link = "<a data-id=\"{$lclabel}\" href=\"{$url}\" target=\"helpFrame\">{$icon}{$text}</a>\n";
        echo "<li class=\"item item-level-{$this->toclevel}\">{$link}</li>\n";
    }
}
echo "</ul>\n";
// On return decrease the toclevel
$this->toclevel -= 1;
