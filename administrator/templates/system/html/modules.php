<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
 * xhtml (divs and font headder tags)
 */
function modChrome_xhtml($module, &$params, &$attribs)
{
	if (!empty ($module->content)) : ?>
		<div class="module<?php echo $params->get('moduleclass_sfx'); ?>">
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
	jimport('joomla.html.pane');
	// Initialize variables
	$user = &JFactory::getUser();
    // TODO: allowAllClose should default true in J!1.6, so remove the array when it does.
	$sliders = &JPane::getInstance('sliders', array('allowAllClose' => true));

	$editAllComponents 	= $user->authorize('core.installer.manage');

	// special handling for components module
	if ($module->module != 'mod_components' || ($module->module == 'mod_components' && $editAllComponents)) {
		$sliders->startPanel(JText::_($module->title), 'module' . $module->id);
		echo $module->content;
		$sliders->endPanel();
	}
}

/*
 * allows for rounded corners
 */
function modChrome_tabs($module, &$params, &$attribs)
{
	jimport('joomla.html.pane');
	// Initialize variables
	$user	= &JFactory::getUser();
	$tabs	= &JPane::getInstance('tabs');

	$editAllComponents 	= $user->authorize('core.installer.manage');

	// special handling for components module
	if ($module->module != 'mod_components' || ($module->module == 'mod_components' && $editAllComponents)) {
			$tabs->startPanel(JText::_($module->title), 'module' . $module->id);
			echo $module->content;
			$tabs->endPanel();
	}
}
?>