<?php
/**
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

include_once (dirname(__FILE__).DS.'/ja_templatetools.php');

$tmpTools = new JA_Tools($this);

# Auto Collapse Divs Functions ##########
$ja_left = $this->countModules('left') || ($tmpTools->getParam(JA_TOOL_MENU) == 1);
$ja_right = $this->countModules('right');

if ($ja_left && $ja_right) {
	$divid = '';
	} elseif ($ja_left) {
	$divid = '-fr';
	} elseif ($ja_right) {
	$divid = '-fl';
	} else {
	$divid = '-f';
}

$curidx = $tmpTools->getCurrentMenuIndex();
//if ($curidx) $curidx--;

//Calculate the width of template
$tmpWidth = '';
$tmpWrapMin = '100%';
switch ($tmpTools->getParam(JA_TOOL_SCREEN)){
	case 'auto':
		$tmpWidth = '97%';
		break;
	case 'fluid':
		$tmpWidth = intval($tmpTools->getParam('ja_screen_width'));
		$tmpWidth = $tmpWidth ? $tmpWidth.'%' : '90%';
		break;
	case 'fix':
		$tmpWidth = intval($tmpTools->getParam('ja_screen_width'));
		$tmpWrapMin = $tmpWidth ? ($tmpWidth+1).'px' : '751px';
		$tmpWidth = $tmpWidth ? $tmpWidth.'px' : '750px';
		break;
	default:
		$tmpWidth = intval($tmpTools->getParam(JA_TOOL_SCREEN));
		$tmpWrapMin = $tmpWidth ? ($tmpWidth+1).'px' : '751px';
		$tmpWidth = $tmpWidth ? $tmpWidth.'px' : '750px';
		break;
}

?>
