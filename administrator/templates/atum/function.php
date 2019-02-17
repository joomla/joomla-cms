<?php

defined('_JEXEC') or die;

function atum_brightness($color, $steps)
{
	$color = trim($color, '#');

	list($red, $green, $blue) = str_split($color, 2);

	$result = [
		str_pad(dechex(min(255, max(0, hexdec($red) + $steps))), 2, '0'),
		str_pad(dechex(min(255, max(0, hexdec($green) + $steps))), 2, '0'),
		str_pad(dechex(min(255, max(0, hexdec($blue) + $steps))), 2, '0')
	];

	return '#' . implode($result);
}
