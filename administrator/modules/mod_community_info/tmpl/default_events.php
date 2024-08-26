<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

if (!isset($module) && isset($displayData)) {
    $module      = $displayData['module'];
    $events_time = $displayData['events_time'];
    $params      = $displayData['params'];
    $events      = $displayData['events'];
}

?>

<table id="collapseEvents<?php echo strval($module->id); ?>" class="table table-sm community-info-events collapse" data-fetch-time="<?php echo $events_time; ?>">
  <caption class="hidden"><?php echo Text::_('MOD_COMMUNITY_INFO_EVENTS_TITLE_FEED'); ?></caption>
  <thead class="hidden">
    <tr>
      <th scope="col"><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
      <th scope="col"><?php echo Text::_('JDATE'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($events as $e => $event) : ?>
        <?php require ModuleHelper::getLayoutPath('mod_community_info', $params->get('layout', 'default') . '_events_item'); ?>
    <?php endforeach; ?>
  </tbody>
</table>
