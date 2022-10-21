<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_dashboard
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$totalRequests  = 0;
$activeRequests = 0;

?>
<table class="table" id="<?php echo str_replace(' ', '', $module->title) . $module->id; ?>">
    <caption class="visually-hidden"><?php echo $module->title; ?></caption>
    <thead>
        <tr>
            <th scope="col" class="w-40"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_TYPE'); ?></th>
            <th scope="col" class="w-40"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_STATUS'); ?></th>
            <th scope="col" class="w-20"><?php echo Text::_('COM_PRIVACY_DASHBOARD_HEADING_REQUEST_COUNT'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($list)) : ?>
            <?php foreach ($list as $i => $item) : ?>
                <?php if (in_array($item->status, array(0, 1))) : ?>
                    <?php $activeRequests += $item->count; ?>
                <?php endif; ?>
                <?php $totalRequests += $item->count; ?>
            <tr>
                <th scope="row">
                    <a href="<?php echo Route::_('index.php?option=com_privacy&view=requests&filter[request_type]=' . $item->request_type . '&filter[status]=' . $item->status); ?>">
                        <?php echo Text::_('COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_' . $item->request_type); ?>
                    </a>
                </th>
                <td>
                    <?php echo HTMLHelper::_('privacy.statusLabel', $item->status); ?>
                </td>
                <td>
                    <span class="badge bg-info"><?php echo $item->count; ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else : ?>
        <tr>
            <td colspan="3">
                <?php echo Text::_('COM_PRIVACY_DASHBOARD_NO_REQUESTS'); ?>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php if (count($list)) : ?>
    <div class="row p-3">
        <div class="col-md-6"><?php echo Text::plural('COM_PRIVACY_DASHBOARD_BADGE_TOTAL_REQUESTS', $totalRequests); ?></div>
        <div class="col-md-6"><?php echo Text::plural('COM_PRIVACY_DASHBOARD_BADGE_ACTIVE_REQUESTS', $activeRequests); ?></div>
    </div>
<?php endif; ?>
