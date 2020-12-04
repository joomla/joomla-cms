<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>

<fieldset class="<?php echo !empty($displayData->formclass) ? $displayData->formclass : 'form-horizontal'; ?>">
	<legend><?php echo $displayData->name; ?></legend>
	<details>
		<summary class="filter-notes"><?php echo Text::_('COM_CONFIG_TEXT_FILTERS_SUMMARY'); ?></summary>
		<div class="filter-notes"><?php echo Text::_('COM_CONFIG_TEXT_FILTERS_DESC'); ?></div>
	</details>
	<details>
		<summary class="filter-notes"><?php echo Text::_('JGLOBAL_FILTER_TYPE_LABEL'); ?></summary>
		<div class="filter-notes"><?php echo Text::_('JGLOBAL_FILTER_TYPE_DESC'); ?></div>
	</details>
	<details>
		<summary class="filter-notes"><?php echo Text::_('JGLOBAL_FILTER_TAGS_LABEL'); ?></summary>
		<div class="filter-notes"><?php echo Text::_('JGLOBAL_FILTER_TAGS_DESC'); ?></div>
	</details>
	<details>
		<summary class="filter-notes"><?php echo Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL'); ?></summary>
		<div class="filter-notes"><?php echo Text::_('JGLOBAL_FILTER_ATTRIBUTES_DESC'); ?></div>
	</details>
	<?php $fieldsnames = explode(',', $displayData->fieldsname); ?>
	<?php foreach ($fieldsnames as $fieldname) : ?>
		<?php foreach ($displayData->form->getFieldset($fieldname) as $field) : ?>
			<div class="table-responsive"><?php echo $field->input; ?></div>
		<?php endforeach; ?>
	<?php endforeach; ?>
</fieldset>
