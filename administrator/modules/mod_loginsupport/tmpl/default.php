<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_loginsupport
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<section class="loginsupport">
	<p><?php echo Text::_('MOD_LOGINSUPPORT_HEADLINE'); ?></p>
	<ul class="list-unstyled">
		<li><a href="<?php echo $params->get('forum_url') ?>" target="_blank" rel="nofollow noopener">
			<?php echo Text::_('MOD_LOGINSUPPORT_FORUM'); ?></a>
		</li>
		<li><a href="<?php echo $params->get('documentation_url') ?>" target="_blank" rel="nofollow noopener">
			<?php echo Text::_('MOD_LOGINSUPPORT_DOCUMENTATION'); ?></a>
		</li>
		<li><a href="<?php echo $params->get('news_url') ?>" target="_blank" rel="nofollow noopener">
			<?php echo Text::_('MOD_LOGINSUPPORT_NEWS'); ?></a>
		</li>
	</ul>
</section>
