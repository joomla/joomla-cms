<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params             = $this->params;

$displayGroups      = $params->get('show_user_custom_fields');
$userFieldGroups    = array();
?>

<?php if (!$displayGroups || !$this->contactUser) : ?>
	<?php return; ?>
<?php endif; ?>

<?php foreach ($this->contactUser->fields as $field) :?>
	<?php if (!in_array('-1', $displayGroups) && !in_array($field->catid, $displayGroups)) : ?>
		<?php continue; ?>
	<?php endif; ?>
	<?php if (!key_exists($field->category_title, $userFieldGroups)) : ?>
		<?php $userFieldGroups[$field->category_title] = array();?>
	<?php endif; ?>
	<?php $userFieldGroups[$field->category_title][] = $field;?>
<?php endforeach; ?>

<?php foreach ($userFieldGroups as $categoryTitle => $fields) :?>
	<?php $id = JApplicationHelper::stringURLSafe($categoryTitle); ?>
	<?php if ($this->params->get('presentation_style') == 'sliders') :
		echo JHtml::_('sliders.panel', $categoryTitle ?: JText::_('COM_CONTACT_USER_FIELDS'), 'display-' . $id); ?>
	<?php endif; ?>
	<?php if ($this->params->get('presentation_style') == 'tabs') : ?>
		<?php echo JHtmlTabs::panel($categoryTitle ?: JText::_('COM_CONTACT_USER_FIELDS'), 'display-' . $id); ?>
	<?php endif; ?>
	<?php if ($this->params->get('presentation_style') == 'plain'):?>
		<?php echo '<h3>'. ( $categoryTitle ?: JText::_('COM_CONTACT_USER_FIELDS')).'</h3>'; ?>
	<?php endif; ?>

	<div class="contact-profile" id="user-custom-fields-<?php echo $id; ?>">
		<dl class="dl-horizontal">
		<?php foreach ($fields as $field) :?>
			<?php if (!$field->value) : ?>
				<?php continue; ?>
			<?php endif; ?>

			<?php echo '<dt>' . $field->label . '</dt>'; ?>
			<?php echo '<dd>' . $field->value . '</dd>'; ?>
		<?php endforeach; ?>
		</dl>
	</div>

<?php endforeach; ?>
