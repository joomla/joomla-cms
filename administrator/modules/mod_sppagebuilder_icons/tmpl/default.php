<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');
?>

<div id="sppagebuilder-wrap" class="clearfix">
	<div class="icon-wrapper">
		<div class="icon">
			<a href="<?php echo JRoute::_('index.php?option=com_sppagebuilder&task=page.add'); ?>">
				<img alt="<?php echo JText::_('SPPAGEBUILDER_ADD_PAGE'); ?>" src="<?php echo JURI::root(true); ?>/administrator/modules/mod_sppagebuilder_icons/tmpl/images/page.png" />
				<span><?php echo JText::_('SPPAGEBUILDER_ADD_PAGE'); ?></span>
			</a>
		</div>
	</div>
	<div class="icon-wrapper">
		<div class="icon">
			<a href="<?php echo JRoute::_('index.php?option=com_sppagebuilder'); ?>">
				<img alt="<?php echo JText::_('SPPAGEBUILDER_PAGES'); ?>" src="<?php echo JURI::root(true); ?>/administrator/modules/mod_sppagebuilder_icons/tmpl/images/pages.png" />
				<span><?php echo JText::_('SPPAGEBUILDER_PAGES'); ?></span>
			</a>
		</div>
	</div>
</div>

