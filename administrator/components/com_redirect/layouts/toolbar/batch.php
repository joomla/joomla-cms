<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.core');

$title = $displayData['title'];

?>
<button type="button" data-toggle="modal" onclick="{document.getElementById('collapseModal').open(); return true;}" class="btn btn-sm btn-primary">
	<span class="icon-checkbox-partial" aria-hidden="true"></span>
	<?php echo $title; ?>
</button>
