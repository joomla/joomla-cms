<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
?>

<form action="<?php echo Route::_('index.php?option=com_messages&view=config'); ?>" method="post" name="adminForm" id="message-form" class="form-validate">
	<div class="form-grid">
		<div class="card">
			<div class="card-body">
				<fieldset class="options-form">
					<legend><?php echo Text::_('COM_MESSAGES_CONFIG_FORM'); ?></legend>
					<?php echo $this->form->renderField('lock'); ?>
					<?php echo $this->form->renderField('mail_on_new'); ?>
					<?php echo $this->form->renderField('auto_purge'); ?>
				</fieldset>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
