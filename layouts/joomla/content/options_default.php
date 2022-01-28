<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

?>

<fieldset class="<?php echo !empty($displayData->formclass) ? $displayData->formclass : ''; ?>">
	<legend><?php echo $displayData->name; ?></legend>
	<?php if (!empty($displayData->description)) : ?>
		<p><?php echo $displayData->description; ?></p>
	<?php endif; ?>
	<?php $fieldsnames = explode(',', $displayData->fieldsname); ?>
		<div class="form-grid">
		<?php foreach ($fieldsnames as $fieldname) : ?>
			<?php foreach ($displayData->form->getFieldset($fieldname) as $field) : ?>
				<?php $datashowon = ''; ?>
				<?php $groupClass = $field->type === 'Spacer' ? ' field-spacer' : ''; ?>
				<?php if ($field->showon) : ?>
					<?php $wa->useScript('showon'); ?>
					<?php $datashowon = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\''; ?>
				<?php endif; ?>

					<?php if (isset($displayData->showlabel)) : ?>
					<div class="control-group<?php echo $groupClass; ?>"<?php echo $datashowon; ?>>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php else : ?>
						<?php echo $field->renderField(); ?>
					<?php endif; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</div>
</fieldset>
