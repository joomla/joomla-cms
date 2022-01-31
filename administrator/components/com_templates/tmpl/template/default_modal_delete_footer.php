<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$input = Factory::getApplication()->input;
?>
<form method="post" action="">
	<input type="hidden" name="option" value="com_templates">
	<input type="hidden" name="task" value="template.delete">
	<input type="hidden" name="id" value="<?php echo $input->getInt('id'); ?>">
	<input type="hidden" name="file" value="<?php echo $this->file; ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
	<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></button>
	<button type="submit" class="btn btn-danger"><?php echo Text::_('COM_TEMPLATES_BUTTON_DELETE'); ?></button>
</form>
