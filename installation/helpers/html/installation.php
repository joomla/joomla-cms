<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla Installation HTML Helper Class.
 *
 * @static
 * @package		Joomla.Installation
 * @since		1.6
 */
class JHtmlInstallation
{
	/**
	 */
	static function stepbar($on = 1)
	{
		$html = '<h1>'.JText::_('Steps').'</h1>' .
			'<div class="step-'.($on == 1 ? 'on' : 'off').'">'.JText::_('Instl_Step_1_Label').'</div>' .
			'<div class="step-'.($on == 2 ? 'on' : 'off').'">'.JText::_('Instl_Step_2_Label').'</div>' .
			'<div class="step-'.($on == 3 ? 'on' : 'off').'">'.JText::_('Instl_Step_3_Label').'</div>' .
			'<div class="step-'.($on == 4 ? 'on' : 'off').'">'.JText::_('Instl_Step_4_Label').'</div>' .
			'<div class="step-'.($on == 5 ? 'on' : 'off').'">'.JText::_('Instl_Step_5_Label').'</div>' .
			'<div class="step-'.($on == 6 ? 'on' : 'off').'">'.JText::_('Instl_Step_6_Label').'</div>' .
			'<div class="step-'.($on == 7 ? 'on' : 'off').'">'.JText::_('Instl_Step_7_Label') .'</div>';
		return $html;
	}
}