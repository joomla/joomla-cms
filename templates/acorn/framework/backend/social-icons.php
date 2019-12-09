<?php

/**
 * @package     acorn.Framework
 * @subpackage  Social Icons Tab
 *
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/*
 * $socialIcons is used quite a bit including in the test to load this file so
 * we set its value in init.php instead ( $socialIcons = $this->params->get('socialIcons'); )
 */

/* Social Icons */
$socialiconsLocation      = $this->params->get('socialiconsLocation');
$socialiconscolumnReverse = $this->params->get('socialiconscolumnReverse');

// Where do we need the social icons?
if ($socialiconsLocation === 'header')
{
	$socialiconsFooter = false;
	$socialiconsNav    = false;
	$socialiconsHeader = true;
}
elseif ($socialiconsLocation === 'footer')
{
	$socialiconsFooter = true;
	$socialiconsNav    = false;
	$socialiconsHeader = false;
}
elseif ($socialiconsLocation === 'navbar')
{
	$socialiconsFooter = false;
	$socialiconsNav    = true;
	$socialiconsHeader = false;
}

/* get the social icons */
$socialiconsData = '<ul class="' . $socialiconsLocation . ' social-icons list-unstyled hidden-xs" aria-hidden="true">';

/* get social array */
$chooser        = $this->params->get('socialArray');
$json           = json_decode($chooser, true);
$filtered_array = group_by_key($json);

$sum = 0;

foreach ($filtered_array as $index => $value)
{
	$name  = strtolower($value[0]);
	$class = $value[1];
	$url   = $value[2];

// Lets create our social icons layout ONCE instead of two or 3 times.  Much less code.

	if ($url)
	{
		if ($class)
		{
			$socialiconsData .= '<li><a href = "' . $url . '" target = "_blank"><span class="' . $class . '"></span></a></li>';

		}
		else
		{
			$socialiconsData .= '<li class = "icon_' . $name . '">'
				. '<a href = "' . $url . '" target = "_blank" >'
				. '<i class="icon-' . $name . '" ></i>'
				. '</a>'
				. '</li>';
		}
	}
}

$socialiconsData .= "</ul>";
