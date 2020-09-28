<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$icon = empty($displayData['icon']) ? 'dot-circle' : preg_replace('#\.[^ .]*$#', '', $displayData['icon']);

if ($icon === 'generic')
{
	$icon = 'dot-circle';
}

$icon = stristr($icon, "joomla") ? str_ireplace("joomla", "fab fa-joomla", $icon) : "fas fa-" . $icon;
?>
<h1 class="page-title">
	<?php echo HTMLHelper::icon($icon); ?>
	<?php echo $displayData['title']; ?>
</h1>
