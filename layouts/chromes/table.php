<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Module chrome that wraps the module in a table
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
?>
<table
    class="moduletable <?php echo htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8'); ?>">
    <?php if ((bool) $module->showtitle) : ?>
        <tr>
            <th>
                <?php echo $module->title; ?>
            </th>
        </tr>
    <?php endif; ?>
    <tr>
        <td>
            <?php echo $module->content; ?>
        </td>
    </tr>
</table>
