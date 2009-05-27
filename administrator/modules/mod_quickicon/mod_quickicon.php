<?php
/**
 * @version		$Id: mod_quickicon.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

if (!defined('_JOS_QUICKICON_MODULE'))
{
	/** ensure that functions are declared only once */
	define('_JOS_QUICKICON_MODULE', 1);

	function quickiconButton($link, $image, $text)
	{
		$app		= &JFactory::getApplication();
		$lang		= &JFactory::getLanguage();
		$template	= $app->getTemplate();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $link; ?>">
					<?php echo JHtml::_('image.site',  $image, '/templates/'. $template .'/images/header/', NULL, NULL, $text); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
		<?php
	}

	?>
	<div id="cpanel">
		<?php
		$link = 'index.php?option=com_content&amp;task=add';
		quickiconButton($link, 'icon-48-article-add.png', JText::_('Add New Article'));

		$link = 'index.php?option=com_content';
		quickiconButton($link, 'icon-48-article.png', JText::_('Article Manager'));

		$link = 'index.php?option=com_frontpage';
		quickiconButton($link, 'icon-48-frontpage.png', JText::_('Frontpage Manager'));

		$link = 'index.php?option=com_sections&amp;scope=content';
		quickiconButton($link, 'icon-48-section.png', JText::_('Section Manager'));

		$link = 'index.php?option=com_categories&amp;section=com_content';
		quickiconButton($link, 'icon-48-category.png', JText::_('Category Manager'));

		$link = 'index.php?option=com_media';
		quickiconButton($link, 'icon-48-media.png', JText::_('Media Manager'));

		// Get the current JUser object
		$user = &JFactory::getUser();

		if ($user->authorize('core.menus.manage')) {
			$link = 'index.php?option=com_menus';
			quickiconButton($link, 'icon-48-menumgr.png', JText::_('Menu Manager'));
		}

		if ($user->authorize('core.languages.manage')) {
			$link = 'index.php?option=com_languages&amp;client=0';
			quickiconButton($link, 'icon-48-language.png', JText::_('Language Manager'));
		}

		if ($user->authorize('core.users.manage')) {
			$link = 'index.php?option=com_users';
			quickiconButton($link, 'icon-48-user.png', JText::_('User Manager'));
		}

		if ($user->authorize('core.config.manage')) {
			$link = 'index.php?option=com_config';
			quickiconButton($link, 'icon-48-config.png', JText::_('Global Configuration'));
		}
		?>
	</div>
	<?php
}