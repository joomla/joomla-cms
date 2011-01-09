<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
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
		$html = '<h1>'.JText::_('INSTL_STEPS_TITLE').'</h1>' .
			'<div class="step-'.($on == 1 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_1_LABEL').'</div>' .
			'<div class="step-'.($on == 2 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_2_LABEL').'</div>' .
			'<div class="step-'.($on == 3 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_3_LABEL').'</div>' .
			'<div class="step-'.($on == 4 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_4_LABEL').'</div>' .
			'<div class="step-'.($on == 5 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_5_LABEL').'</div>' .
			'<div class="step-'.($on == 6 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_6_LABEL').'</div>' .
			'<div class="step-'.($on == 7 ? 'on' : 'off').'">'.JText::_('INSTL_STEP_7_LABEL').'</div>';
		return $html;
	}
}