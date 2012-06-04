<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHTML::_('behavior.modal');
//load user_profile plugin language
$lang = JFactory::getLanguage();
$lang->load( 'plg_user_profile', JPATH_ADMINISTRATOR );
?>
<div class="profile-edit<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

<form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);?>
	<?php if (count($fields)):?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo JText::_($fieldset->label); ?></legend>
		<?php endif;?>
		<dl>
		<?php foreach ($fields as $field):// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				<dt>
					<?php // Added for TOS article
					if ($field->name == "jform[profile][tos]")
					{
						if ($fieldset->label == "PLG_USER_PROFILE_SLIDER_LABEL")
						{
							$plugin = JPluginHelper::getPlugin('user','profile');
							$pluginParams = new JRegistry();
							$pluginParams->loadString($plugin->params);
							$tosarticle = $pluginParams->get('register-require_tos_article', '1');

							$doc = JFactory::getDocument();
							$toscss1 = "#jform_profile_tos {width: 18em; margin: 0 !important; padding: 0 2px !important;}";
							$toscss2 = "#jform_profile_tos input {margin:0 5px 0 0 !important; width:10px !important;}";
							
							$doc->addStyleDeclaration($toscss1);
							$doc->addStyleDeclaration($toscss2);
							?>

							<a class="modal" title="" href="index.php?option=com_content&amp;view=article&amp;layout=modal&amp;id=<?php echo $tosarticle; ?>&amp;tmpl=component" rel="{handler: 'iframe', size: {x:800, y:500}}"><?php echo JText::_('PLG_USER_PROFILE_FIELD_TOS_LABEL') ?></a>

							<?php if ($field->required): ?>
								<span class="star">&#160;*</span>
							<?php endif;
						}		  
					} 
					else echo $field->label;
					?>
					<?php if (!$field->required && $field->type!='Spacer' && $field->name!='jform[username]'): ?>
						<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
					<?php endif; ?>
				</dt>
				<dd><?php echo $field->input; ?></dd>
			<?php endif;?>
		<?php endforeach;?>
		</dl>
	</fieldset>
	<?php endif;?>
<?php endforeach;?>

		<div>
			<button type="submit" class="validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
			<?php echo JText::_('COM_USERS_OR'); ?>
			<a href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>

			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="profile.save" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
