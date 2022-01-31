<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_loginsupport
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<section class="loginsupport">
	<p><?php echo Text::_('MOD_LOGINSUPPORT_HEADLINE'); ?></p>
	<ul class="list-unstyled">
		<li>
			<?php echo HTMLHelper::link(
				$params->get('forum_url'),
				Text::_('MOD_LOGINSUPPORT_FORUM'),
				[
					'target' => '_blank',
					'rel'    => 'nofollow noopener',
					'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', Text::_('MOD_LOGINSUPPORT_FORUM'))
				]
			); ?>
		</li>
		<li>
			<?php echo HTMLHelper::link(
				$params->get('documentation_url'),
				Text::_('MOD_LOGINSUPPORT_DOCUMENTATION'),
				[
					'target' => '_blank',
					'rel'    => 'nofollow noopener',
					'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', Text::_('MOD_LOGINSUPPORT_DOCUMENTATION'))
				]
			); ?>
		</li>
		<li>
			<?php echo HTMLHelper::link(
				$params->get('news_url'),
				Text::_('MOD_LOGINSUPPORT_NEWS'),
				[
					'target' => '_blank',
					'rel'    => 'nofollow noopener',
					'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', Text::_('MOD_LOGINSUPPORT_NEWS'))
				]
			); ?>
		</li>
	</ul>
</section>
