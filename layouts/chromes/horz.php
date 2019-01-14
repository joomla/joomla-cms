<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Module chrome that wraps the tabled module output in a <td> tag of another table
 */
defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
?>
<table cellspacing="1" cellpadding="0" width="100%">
	<tr>
		<td>
			<?php LayoutHelper::render('chromes.table', $displayData); ?>
		</td>
	</tr>
</table>
