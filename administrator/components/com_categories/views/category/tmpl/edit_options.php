<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php foreach($this->form->getFields('params') as $field): ?>
	<?php if ($field->hidden): ?>
		<?php echo $field->input; ?>
	<?php else: ?>
		<ol>
			<li class="paramlist_key">
				<?php echo $field->label; ?>
			</li>
			<li class="paramlist_value">
				<?php echo $field->input; ?>
			</li>
		</ol>
	<?php endif; ?>
<?php endforeach; ?>
