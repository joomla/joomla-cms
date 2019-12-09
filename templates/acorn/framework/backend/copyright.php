<?php

	/**
	 * @package     acorn.Framework
	 * @subpackage  Copyright Tab
	 *
	 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
	defined('_JEXEC') or die;

// Variables
	$copyright = $this->params->get('copyright');
	$copytext = $this->params->get('copyrightText', "");
	$copyrightreplace = $this->params->get('copyrightreplaceText', '(c)');
	$copyrightstartYear = $this->params->get('copyrightstartYear', '');
	$copyrightPosition = $this->params->get('copyrightPosition');

	/**
	 * ==================================================
	 * Automatic Copyright function
	 * ==================================================
	 */
	if ($copyright) {
		$thisYear = date('Y');
		if ($copyrightstartYear > $thisYear) {
			$copyrightstartYear = $thisYear;
		}
		if ($copyrightstartYear && $copyrightstartYear != $thisYear) {
			$thisYear = $copyrightstartYear . ' - ' . $thisYear;
		}
		$copytext = str_replace($copyrightreplace, '&copy; ' . $thisYear, $copytext);
	} else {
		$copytext = "";
	}

	if ($copyrightPosition) {
		$copyrightCss = "#copyright .copyright{\n	text-align: " . $copyrightPosition . ";\n}";
	}
