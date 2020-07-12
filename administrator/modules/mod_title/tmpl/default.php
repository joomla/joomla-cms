<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_title
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if (!empty($title)) : ?>
<div class="d-flex align-items-center px-3">
	<div class="container-title">
		<?php echo $title; ?>
	</div>
</div>
<?php endif; ?>
