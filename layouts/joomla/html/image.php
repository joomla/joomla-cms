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
 * @var   array  $displayData  Array with all the valid attribute for the image element.
 *                             Eg: src,class,alt,width,height,loading,decoding,style,data-*
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$img    = HTMLHelper::_('cleanImageURL', $displayData['src']);
$hasSrc = !empty($displayData['src']);
$hasAlt = !empty($displayData['alt']);

if ($img->width > 0 && $img->height > 0) {
  $displayData['width'] = $img->width;
  $displayData['height'] = $img->height;
  $displayData['loading'] = 'lazy';
}

$src = $hasSrc ? htmlspecialchars($displayData['src'], ENT_QUOTES, 'UTF-8') . ' ' : '';
$alt = $hasAlt ? htmlspecialchars($displayData['alt'], ENT_QUOTES, 'UTF-8') . ' ': '';

if ($hasSrc)
{
  unset($displayData['src']);
}

if ($hasAlt) {
  unset($displayData['alt']);
}

echo '<img ' . $src . $alt . ArrayHelper::toString($displayData) . '>';
