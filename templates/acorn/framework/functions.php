<?php

/**
 * @package     acorn.Framework
 * @subpackage  acorn
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * ==================================================
 * Framework Functions
 * @since 1.0
 * ==================================================
 */
/* =========== BEGIN CHOOSER FUNCTION ================== */
/**
 * @param $array
 *
 * @return array
 *
 * @since 1.0
 *
 * @description
 * $chooser = $this->params->get('yourfieldname');
 * $json = json_decode($chooser, true);
 * if($json){$filtered_array = group_by_key($json);}
 * either pull value from $filtered_array[R][C] where R = row & C = column or
 * run through for each extraction ( see google fonts ) R&C start with 0!
 * irregardless as of acorn-B70 each call will need a unique variable declaration.
 */

if (!function_exists('group_by_key'))
{
	function group_by_key($array)
	{
		$result = array();
		/* Safety Trap - Alan */
		if (!is_array($array))
		{
			return $result;
		}

		foreach ($array as $sub)
		{
			foreach ($sub as $k => $v)
			{
				$result[$k][] = $v;
			}
		}

		return $result;
	}
}


// =========== BEGIN CUSTOM JS FILES FUNCTION ==================
function getCustomJsFiles($templatefilePath, $HTMLHelperDebug)
{
	/* Get list of files in the folder that are .js -- this function is broken so .js.disabled is still returned */
	$files = Folder::files($templatefilePath . '/js/custom/', '.js', $recurse = false);

	if (empty($files))
	{
		Factory::getApplication()->enqueueMessage(Text::_('TPL_ACORN_CUSTOMJS_FILES_ERROR'), 'danger');

		return false;
	}

	/* break array of files into single filenames */
	foreach ($files as $value)
	{
		HTMLHelper::_('script', 'custom/' . $value, $HTMLHelperDebug);
	}

	/* next file */

	return true;
	/* end of file checking */
}

// =========== BEGIN PX PARAMETER VALIDATION FUNCTION ==================
/* Check for whether a size type ( px, rem, etc ) is used or not.
	if not then it add's px assuming thats what they should've put.
	if they did put auto, then it just uses that.
	also forces any letters used to lowercase.
 */
function checkPX($check)
{
	if ($check !== 'auto')
	{
		if (preg_match('/(\-?[0-9]+)\s*(px|em|%|vh|rem|pt|cm|mm|in|pc|ex|ch|vw|vmin|vmax)?/', $check, $match))
		{
			if (isset($match[2]))
			{
				$unit = $match[2];
			}
			else
			{
				$unit = 'px';
			}
			$check = trim($match[1]) . $unit;
		}
	}

	return strtolower($check);
}
// =========== END PX PARAMETER VALIDATION FUNCTION ==================
