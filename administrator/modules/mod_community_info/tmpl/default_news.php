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

if(!isset($module) && isset($displayData)) {
  $module    = $displayData['module'];
  $news_time = $displayData['news_time'];
  $params    = $displayData['params'];
  $news      = $displayData['news'];
}

?>

<table id="collapseNews<?php echo strval($module->id); ?>" class="table community-info-news collapse" data-fetch-time="<?php echo $news_time; ?>">
  <caption class="hidden"><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_TITLE_FEED'); ?></caption>
  <thead class="hidden">
    <tr>
      <th scope="col"><?php echo Text::_('JGLOBAL_TITLE'); ?></th>
      <th scope="col"><?php echo Text::_('JGLOBAL_PUBLISHED_DATE'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($news as $n => $article) : ?>
      <?php require ModuleHelper::getLayoutPath('mod_community_info', $params->get('layout', 'default') . '_news_item'); ?>
    <?php endforeach; ?>
  </tbody>
</table>
