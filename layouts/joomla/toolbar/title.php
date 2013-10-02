<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<h1 class="page-title">
	<?php if (!empty($displayData['icon'])) : ?>
		<span class="icon-<?php echo preg_replace('#\.[^ .]*$#', '', $displayData['icon']); ?>"></span>
	<?php endif; ?>
	<?php echo $displayData['title']; ?>
</h1>
