<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

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
	$user		=& JFactory::getUser();
	$sliders	=& JPane::getInstance('sliders');

	$editAllComponents 	= $user->authorize( 'administration', 'edit', 'components', 'all' );

	// special handling for components module
	if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
		$sliders->startPanel( JText::_( $module->title ), 'module' . $module->id );
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
	$user	=& JFactory::getUser();
	$tabs	=& JPane::getInstance('tabs');

	$editAllComponents 	= $user->authorize( 'administration', 'edit', 'components', 'all' );

	// special handling for components module
	if ( $module->module != 'mod_components' || ( $module->module == 'mod_components' && $editAllComponents ) ) {
			$tabs->startPanel( JText::_( $module->title ), 'module' . $module->id );
			echo $module->content;
			$tabs->endPanel();
	}
}
?>