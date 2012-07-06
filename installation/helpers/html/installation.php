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
		$tabs = array(
			'language',
			'preinstall',
			'database',
			'filesystem',
			'site',
			'complete'
		);
		$html = array();
		$html[] = '<ul class="nav nav-tabs">';
		foreach($tabs as $tab) {
			$html[] = self::getTab($tab, $tabs);
		}
		$html[] = '</ul>';
		return implode('', $html);
	}
	public static function getTab($id, &$tabs)
	{
		$num = self::getNumber($id, $tabs);
		$view = self::getNumber(JRequest::getWord('view'), $tabs);
		$tab = '<span class="badge">'.$num.'</span> '.JText::_('INSTL_STEP_'.strtoupper($id).'_LABEL');
		if ($view+1 == $num) {
			$tab = '<a href="#" onclick="Install.submitform();">'.$tab.'</a>';
		} else if ($view < $num) {
			$tab = '<span>'.$tab.'</span>';
		} else  {
			$tab = '<a href="index.php?view='.$id.'">'.$tab.'</a>';
		}
		return '<li class="step'.($num == $view ? ' active' : '').'" id="'.$id.'">'.$tab.'</li>';
	}

	public static function getNumber($id, &$tabs)
	{
		$num = (int) array_search($id, $tabs);
		$num++;
		return $num;
	}
}
