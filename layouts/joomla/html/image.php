<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Layout variables
 * -----------------
 * @var   array  $displayData  Array with all the given attributes for the image element.
 *                             Eg: src, class, alt, width, height, loading, decoding, style, data-*
 *                             Note: only the alt and src attributes are escaped by default!
 */

if (isset($displayData['src']))
{
	$displayData['src'] = $this->escape($displayData['src']);
}

if (isset($displayData['alt']))
{
	if ($displayData['alt'] === false)
	{
		unset($displayData['alt']);
	}
	else
	{
		$displayData['alt'] = $this->escape($displayData['alt']);
	}
}

echo '<img ' . JArrayHelper::toString($displayData) . '>';
