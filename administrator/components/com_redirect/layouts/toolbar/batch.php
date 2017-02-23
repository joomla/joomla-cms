<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('behavior.core');

$title = $displayData['title'];

?>
<button data-toggle="modal" onclick="{jQuery( '#collapseModal' ).modal('show'); return true;}" class="btn btn-outline-primary btn-sm">
	<span class="icon-checkbox-partial" title="<?php echo $title; ?>"></span>
	<?php echo $title; ?>
</button>
