<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$backtrace = $displayData['backtrace'];
$j = 1;
?>
<table cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th colspan="3"><strong>Call stack</strong></th>
        </tr>
        <tr>
            <th>#</th>
            <th>Function</th>
            <th>Location</th>
        </tr>
    </thead>
    <tbody>
        <?php
        for ($i = count($backtrace) - 1; $i >= 0; $i--)
        {
            $link = '&#160;';

            if (isset($backtrace[$i]['file']))
            {
                $link = $this->formatLink($backtrace[$i]['file'], $backtrace[$i]['line']);
            }

            $html[] = '<tr>';
            $html[] = '<td>' . $j . '</td>';

            if (isset($backtrace[$i]['class']))
            {
                $html[] = '<td>' . $backtrace[$i]['class'] . $backtrace[$i]['type'] . $backtrace[$i]['function'] . '()</td>';
            }
            else
            {
                $html[] = '<td>' . $backtrace[$i]['function'] . '()</td>';
            }

            $html[] = '<td>' . $link . '</td>';
            $html[] = '</tr>';

            $j++;
        }
        ?>
    </tbody>
</table>