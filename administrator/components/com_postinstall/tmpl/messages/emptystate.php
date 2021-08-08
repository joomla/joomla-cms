<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$adminFormClass = count($this->extension_options) > 1 ? 'form-inline mb-3' : 'visually-hidden';
?>

<form action="index.php" method="post" name="adminForm" class="<?php echo $adminFormClass; ?>" id="adminForm">
	<input type="hidden" name="option" value="com_postinstall">
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
	<label for="eid" class="me-sm-2"><?php echo Text::_('COM_POSTINSTALL_MESSAGES_FOR'); ?></label>
	<?php echo HTMLHelper::_('select.genericlist', $this->extension_options, 'eid', array('onchange' => 'this.form.submit()', 'class' => 'form-select'), 'value', 'text', $this->eid, 'eid'); ?>
</form>

<?php
$displayData = [
	'textPrefix' => 'COM_POSTINSTALL',
	'formURL'    => 'index.php?option=com_postinstall',
	'icon'       => 'icon-bell',
	'createURL'  => 'index.php?option=com_postinstall&view=messages&task=message.reset&eid=' . $this->eid . '&' . $this->token . '=1',
];

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
