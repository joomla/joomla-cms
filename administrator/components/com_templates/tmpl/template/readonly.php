<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$input = Factory::getApplication()->input;
?>
<form action="<?php echo Route::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm">
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'description')); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'description', Text::_('COM_TEMPLATES_TAB_DESCRIPTION')); ?>
			<?php echo $this->loadTemplate('description'); ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
