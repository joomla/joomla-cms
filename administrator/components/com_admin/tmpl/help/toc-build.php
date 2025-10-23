<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Help;

use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class Toc
{
    /**
     * The id of each ul submenu - incremented for each.
     *
     * @var int
     */
    protected $tocid = 1000;

    /**
     * The level of menu / submenu items.
     *
     * @var int
     */
    protected $toclevel = 1;

    /**
     * The id of each li that contains a sub menu
     *
     * @var int
     */
    protected $liid = 0;

    /**
     * Method to get the table of contents
     *
     * @return  array  Table of contents
     */
    public function &getToc()
    {
        require __DIR__ . '/toc-src.php';
        $html =  $this->buildMenu($menu, true);
        return $html;
    }

    protected function buildMenu(array $items, $pass = false): string
    {
        // don't set a ul on the first pass
        if ($pass) {
            $html = "";
        } else {
            $this->tocid += 1;
            // Increase the toclevel on entry
            $this->toclevel += 1;
            if ($this->toclevel > 1) {
                $collapse = ' mm-collapse';
            } else {
                $collapse = '';
            }
            $html = "<ul id=\"collapse{$this->tocid}\" class=\"collapse-level-1{$collapse}\">\n";
        }
        foreach ($items as $label => $value) {
            $text = Text::_('COM_ADMIN_HELP_' . $label);
            $lclabel = strtolower(str_replace('_', '-', $label));
            $this->liid += 1;
            // Numeric keys mean leaf items (not headings)
            if (is_array($value)) {
                $icon = "<span class=\"icon-folder icon-fw\" aria-hidden=\"true\"></span>";
                $wrap_label = "<span class=\"item-title\">{$text}</span>";
                $html .= "<li id=\"li{$this->liid}\" class=\"item parent item-level-{$this->toclevel}\">";
                $html .= "<a href=\"#\" class=\"has-arrow\">";
                $html .= "{$icon}{$wrap_label}</a>\n";
                if (!empty($value)) {
                    $html .= $this->buildMenu($value); // Recursively build sublist
                }
                $html .= "</li>\n";
            } else {
                $icon = "<span class=\"icon-file-alt icon-fw\" aria-hidden=\"true\"></span>";
                // The label is help.joomla.org help key.
                $url = Help::createUrl($label);
                $link = "<a data-id=\"{$lclabel}\" href=\"{$url}\" target=\"helpFrame\">{$icon}{$text}</a>\n";
                $html .= "<li class=\"item item-level-{$this->toclevel}\">{$link}</li>\n";
            }
        }
        // don't set a ul on the first pass
        if ($pass) {
        } else {
            $html .= "</ul>\n";
        }
        // On return decrease the toclevel
        $this->toclevel -= 1;
        return $html;
    }
}
