<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * none (output raw module content)
 */
function modChrome_none($module, &$params, &$attribs)
{
	echo $module->content;
}

/*
 * xhtml (divs and font header tags)
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="module<?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle != 0) : ?>
			<h3><?php echo $module->title; ?></h3>
		<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	<?php endif;
}

/*
 * allows for rounded corners
 */
function modChrome_sliders($module, &$params, &$attribs)
{
	// Initialise variables.
	$user = JFactory::getUser();

	$editAllComponents	= $user->authorise('core.manage', 'com_installer');

	// special handling for components module
	if ($module->module != 'mod_components' || ($module->module == 'mod_components' && $editAllComponents)) {
		echo JHtml::_('sliders.panel',JText::_($module->title), 'module' . $module->id);
		echo $module->content;
	}
}

/*
 * allows for rounded corners
 */
function modChrome_tabs($module, &$params, &$attribs)
{
	// Initialise variables.
	$user	= JFactory::getUser();

	$editAllComponents	= $user->authorise('core.manage', 'com_installer');

	// special handling for components module
	if ($module->module != 'mod_components' || ($module->module == 'mod_components' && $editAllComponents)) {
		echo JHtml::_('tabs.panel', JText::_($module->title), 'module' . $module->id);
		echo $module->content;
	}
}
?>
