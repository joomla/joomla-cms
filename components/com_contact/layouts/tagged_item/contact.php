<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JModelLegacy::addIncludePath('components/com_contact/models');

$item    = $displayData['item'];
$model   = JModelLegacy::getInstance('contact', 'ContactModel');
$contact = $model->getItem($item->content_item_id);
$lang    = JFactory::getLanguage()->load('com_contact');

?>
<h3>
	<a href="<?php echo JRoute::_(TagsHelperRoute::getItemRoute($item->content_item_id, $item->core_alias, $item->core_catid, $item->core_language, $item->type_alias, $item->router)); ?>">
		<?php echo $this->escape($item->core_title); ?>
	</a>
</h3>
<dl class="dl-horizontal">
	<?php if ($contact->address) : ?>
		<dt><?php echo JText::_('COM_CONTACT_ADDRESS'); ?></dt>
		<dd><?php echo $contact->address; ?></dd>
	<?php endif; ?>
	<?php if ($contact->country) : ?>
		<dt><?php echo JText::_('COM_CONTACT_COUNTRY'); ?></dt>
		<dd><?php echo $contact->country; ?></dd>
	<?php endif; ?>
</dl>
<?php if ($item->core_images) : ?>
	<img src="<?php echo $item->core_images; ?>" class="ss-image pull-right img-polaroid">
<?php endif; ?>
<?php echo $item->core_body; ?>
