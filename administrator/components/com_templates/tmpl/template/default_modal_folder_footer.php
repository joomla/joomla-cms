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
use Joomla\CMS\Router\Route;

$input = Factory::getApplication()->input;
?>
<form id="deleteFolder" method="post" action="<?php echo Route::_('index.php?option=com_templates&task=template.deleteFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
    <fieldset>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo Text::_('COM_TEMPLATES_TEMPLATE_CLOSE'); ?></button>
        <input type="hidden" class="address" name="address">
        <input type="hidden" name="isMedia" value="0">
        <?php echo HTMLHelper::_('form.token'); ?>
        <button type="submit" class="btn btn-danger"><?php echo Text::_('COM_TEMPLATES_BUTTON_DELETE'); ?></button>
    </fieldset>
</form>
