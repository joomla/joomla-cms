<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!isset($module) && isset($displayData)) {
    $module    = $displayData['module'];
}

?>

<div id="collapseNews<?php echo strval($module->id); ?>" class="community-info-news collapse" data-fetch-time="<?php echo $news_time; ?>">
  <div class="alert alert-info" role="alert">
    <?php echo Text::_('MOD_COMMUNITY_NO_NEWS_FOUND'); ?>
  </div>
</div>
