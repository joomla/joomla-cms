<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');

$input = Factory::getApplication()->input;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form
	action="<?php echo Route::_('index.php?option=com_admin&view=profile&layout=edit&id=' . $this->item->id); ?>"
	method="post"
	name="adminForm"
	id="profile-form"
	enctype="multipart/form-data"
	class="form-validate"
>
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'user_details')); ?>
	<?php foreach ($fieldsets as $fieldset) : ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label)); ?>
		<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
			<?php echo $field->renderField(); ?>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<?php endforeach; ?>
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	<input type="hidden" name="task" value="">
	<input type="hidden" name="return" value="<?php echo $input->get('return', '', 'BASE64'); ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
