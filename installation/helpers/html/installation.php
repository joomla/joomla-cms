<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package  Joomla.Installation
 * @since    1.6
 */
class JHtmlInstallation
{
	public static function stepbar()
	{
		$view = JFactory::getApplication()->input->getWord('view');
		switch ($view) {
			case '':
			case 'language':
				$on = 1;
				break;
			case 'preinstall':
				$on = 2;
				break;
			case 'database':
				$on = 3;
				break;
			case 'filesystem':
				$on = 4;
				break;
			case 'site':
				$on = 5;
				break;
			case 'complete':
				$on = 6;
				break;
			case 'remove':
				$on = 6;
				break;
			default:
				$on = 1;
		}

 		$html = '<ul class="nav nav-tabs">' .
			'<li class="step'.($on == 1 ? ' active' : '').'" id="language"><a href="#"><span class="badge">1</span> '.JText::_('INSTL_STEP_1_LABEL').'</a></li>' .
			'<li class="step'.($on == 2 ? ' active' : '').'" id="preinstall"><a href="#"><span class="badge">2</span> '.JText::_('INSTL_STEP_2_LABEL').'</a></li>' .
			'<li class="step'.($on == 3 ? ' active' : '').'" id="database"><a href="#"><span class="badge">3</span> '.JText::_('INSTL_STEP_4_LABEL').'</a></li>' .
			'<li class="step'.($on == 4 ? ' active' : '').'" id="filesystem"><a href="#"><span class="badge">4</span> '.JText::_('INSTL_STEP_5_LABEL').'</a></li>' .
			'<li class="step'.($on == 5 ? ' active' : '').'" id="site"><a href="#"><span class="badge">5</span> '.JText::_('INSTL_STEP_6_LABEL').'</a></li>' .
			'<li class="step'.($on == 6 ? ' active' : '').'" id="complete"><a href="#"><span class="badge">6</span> '.JText::_('INSTL_STEP_7_LABEL').'</a></li>'.
			'</ul>';
			return $html;
	}
}
