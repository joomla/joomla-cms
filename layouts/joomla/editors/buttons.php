<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$buttons = $displayData;

?>
<div id="editor-xtd-buttons" class="btn-toolbar pull-left" role="toolbar" aria-label="<?php echo JText::_('JTOOLBAR'); ?>">
	<?php if ($buttons) : ?>
		<?php foreach ($buttons as $button) : ?>
			<?php echo $this->sublayout('button', $button); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
