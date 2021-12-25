<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Layout variables
 * -----------------
 * @var   array  $displayData  Array with all the given attributes for the image element.
 *                             Eg: src,class,alt,width,height,loading,decoding,style,data-*
 *                             Note: only the alt attribute is escaped by default!
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$img = HTMLHelper::_('cleanImageURL', $displayData['src']);

$displayData['src'] = $this->escape($img->url);

if (!empty($displayData['alt']))
{
	$displayData['alt'] = $this->escape($displayData['alt']);
}

if (isset($img->width) && $img->width > 0 && isset($img->height) && $img->height > 0)
{
	$displayData['width']  = $img->width;
	$displayData['height'] = $img->height;

	if (empty($displayData['loading']))
	{
		$displayData['loading'] = 'lazy';
	}
}

echo '<img '  . ArrayHelper::toString($displayData) . '>';
