<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  PlgSystemStats             $plugin        Plugin rendering this layout
 * @var  \Joomla\Registry\Registry  $pluginParams  Plugin parameters
 * @var  array                      $statsData     Array containing the data that will be sent to the stats server
 */
?>

<joomla-alert type="info" dismiss="true" class="js-pstats-alert" style="display:none;">
	<h2><?php echo Text::_('PLG_SYSTEM_STATS_LABEL_MESSAGE_TITLE'); ?></h2>
	<p>
		<?php echo Text::_('PLG_SYSTEM_STATS_MSG_JOOMLA_WANTS_TO_SEND_DATA'); ?>
		<a href="#" class="js-pstats-btn-details alert-link"><?php echo Text::_('PLG_SYSTEM_STATS_MSG_WHAT_DATA_WILL_BE_SENT'); ?></a>
	</p>
	<?php
		echo $plugin->render('stats', compact('statsData'));
	?>
	<p><?php echo Text::_('PLG_SYSTEM_STATS_MSG_ALLOW_SENDING_DATA'); ?></p>
	<p class="actions">
		<a href="#" class="btn btn-primary js-pstats-btn-allow-always"><?php echo Text::_('PLG_SYSTEM_STATS_BTN_SEND_ALWAYS'); ?></a>
		<a href="#" class="btn btn-primary js-pstats-btn-allow-once"><?php echo Text::_('PLG_SYSTEM_STATS_BTN_SEND_NOW'); ?></a>
		<a href="#" class="btn btn-primary js-pstats-btn-allow-never"><?php echo Text::_('PLG_SYSTEM_STATS_BTN_NEVER_SEND'); ?></a>
	</p>
</joomla-alert>
