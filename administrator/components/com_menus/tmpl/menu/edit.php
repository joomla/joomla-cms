<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('keepalive')
	->useScript('form.validate');

Text::script('ERROR');
?>
<form action="<?php echo Route::_('index.php?option=com_menus&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" aria-label="<?php echo Text::_('COM_MENUS_MENU_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="main-card">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_MENUS_MENU_DETAILS')); ?>

			<fieldset id="fieldset-details" class="options-form">
				<legend><?php echo Text::_('COM_MENUS_MENU_DETAILS'); ?></legend>

				<div>
					<div>
						<?php
						echo $this->form->renderField('menutype');

						echo $this->form->renderField('description');

						echo $this->form->renderField('client_id');

						echo $this->form->renderField('preset');
						?>
					</div>
				</div>
			</fieldset>

			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'permissions', Text::_('COM_MENUS_FIELDSET_RULES')); ?>
				<fieldset id="fieldset-rules" class="options-form">
					<legend><?php echo Text::_('COM_MENUS_FIELDSET_RULES'); ?></legend>
					<div>
					<?php echo $this->form->getInput('rules'); ?>
					</div>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
