<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Behavior helpers
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UserFx
{
	/**
	 * Sets up the required javascript for introducing a sliding element to the page
	 *
	 * @param	mixed	True (or equivalent) if the slider should be open, otherwise it will be closed
	 * @param	string	The id of the clickable element
	 * @param	string	The id of the sliding element
	 */
	function slider( $open = false, $idClicker ='v_toggle', $idSlider = 'v_slider' )
	{
		// Include mootools
		JHTML::_('behavior.mootools');

		// Convert the open state to a true int
		$open = (int) $open;

		// Get the document
		$document = &JFactory::getDocument();

		// Inject the javascript behaviour into the document HEAD
		$document->addScriptDeclaration( <<<CODE
		//<![CDATA[
		window.addEvent('domready',function() {
			var FxSlider = new Fx.Slide('{$idSlider}');
			$('{$idClicker}').addEvent('click', function(e) {
				e = new Event(e);
				FxSlider.toggle();
				e.stop();
			});
			if (!{$open}) FxSlider.hide();
		});
		//]]>
CODE
		);
	}
}