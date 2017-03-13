<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
ksort($this->files, SORT_STRING);
?>

<ul class='nav nav-list directory-tree'>
	<?php foreach ($this->files as $key => $value): ?>
		<?php if (is_array($value)): ?>
			<li class="folder-select">
				<a class='folder-url nowrap' data-id='<?php echo base64_encode($key); ?>' href=''>
					<span class='icon-folder-close'>&nbsp;<?php $explodeArray = explode('/', $key); echo end($explodeArray); ?></span>
				</a>
				<?php echo $this->folderTree($value); ?>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
