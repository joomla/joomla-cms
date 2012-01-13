<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$fields = $this->form->getFieldset();

unset($fields['jform_db_type']);

?>
<table class="content2 db-table">
	<?php foreach ($fields as $field) : ?>
		<tr>
			<td>
				<?php echo $field->label; ?>
				<br/>
				<?php echo $field->input; ?>
			</td>
			<td>
				<em>
					<?php echo JText::_($field->description); ?>
				</em>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
