<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

$imgAttribs = $image['attributes'];
$img        = HTMLHelper::_('cleanImageURL', $image['src']);

if ($img->width > 0 && $img->height > 0) {
  $imgAttribs['width'] = $img->width;
  $imgAttribs['height'] = $img->height;
  $imgAttribs['loading'] = 'lazy';
}

echo HTMLHelper::_('image', htmlspecialchars($img->url, ENT_QUOTES, 'UTF-8'), htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8'), $image['attributes']);
