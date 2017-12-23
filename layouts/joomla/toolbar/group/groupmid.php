<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$btnClass = $displayData['class'];
?>
<button
	type="button"
	class="btn btn-sm <?php echo $btnClass; ?> dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
	aria-haspopup="true"
	aria-expanded="false"></button>
<div class="dropdown-menu">
