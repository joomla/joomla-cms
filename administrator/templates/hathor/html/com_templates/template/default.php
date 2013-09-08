<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');

$canDo = TemplatesHelper::getActions();
$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">
    jQuery(document).ready(function($){

        // Hide all the folder when the page loads
        $('.folder ul').hide();

        // Show all the lists in the path of an open file
        $('.show > ul').show();

        // Stop the default action of anchor tag on a click event
        $('.folder-url').click(function(event){
            event.preventDefault();
        });

        // Prevent the click event from proliferating
        $('.file').bind('click',function(e){
            e.stopPropagation();
        });

        // Toggle the child indented list on a click event
        $('.folder').bind('click',function(e){
            $(this).children('ul').toggle();
            e.stopPropagation();
        });

        // New file tree
        $('#fileModal .folder-url').bind('click',function(e){
            $('.folder-url').removeClass('selected');
            e.stopPropagation();
            $('#fileModal input.address').val($(this).attr('data-id'));
            $(this).addClass('selected');
        });

        // Folder manager tree
        $('#folderModal .folder-url').bind('click',function(e){
            $('.folder-url').removeClass('selected');
            e.stopPropagation();
            $('#folderModal input.address').val($(this).attr('data-id'));
            $(this).addClass('selected');
        });

    });
</script>
<form action="<?php echo JRoute::_('index.php?option=com_templates&view=templates'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-50 fltlft">
		<fieldset class="adminform" id="template-manager">
			<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_DESCRIPTION');?></legend>

			<?php echo JHtml::_('templates.thumb', $this->template->element, $this->template->client_id); ?>


			<h2><?php echo ucfirst($this->template->element); ?></h2>
			<?php $client = JApplicationHelper::getClientInfo($this->template->client_id); ?>
			<p><?php $this->template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $this->template->element);?></p>
			<p><?php  echo JText::_($this->template->xmldata->description); ?></p>
		</fieldset>
		<fieldset class="adminform" id="template-manager">
			<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_MASTER_FILES');?></legend>

            <?php $this->listDirectoryTree($this->files);?>
		</fieldset>

		<div class="clr"></div>
	</div>

	<div class="width-50 fltrt">

		<fieldset class="adminform" id="template-manager-css">
			<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CSS');?></legend>

			<?php if (!empty($this->files['css'])) : ?>
			<ul>
				<?php foreach ($this->files['css'] as $file) : ?>
				<li>
					<?php if ($canDo->get('core.edit')) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$file->id);?>">
					<?php endif; ?>

						<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_EDIT_CSS', $file->name);?>
					<?php if ($canDo->get('core.edit')) : ?>
					</a>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<!--<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_ADD_CSS');?></a>
			</div>-->

		</fieldset>
		<div class="clr"></div>
		<input type="hidden" name="task" value="" />
	</div>
<div class="width-50 fltrt">
</form>
<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id')); ?>"
		method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform" id="template-manager-css">
		<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY');?></legend>
		<label id="new_name" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_LABEL')?></label>
		<input class="inputbox" type="text" id="new_name" name="new_name"  />
		<button type="submit"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY'); ?></button>
	</fieldset>
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
<div class="width-50 fltrt">

    <fieldset class="adminform" id="template-manager-css">
        <legend>
            <?php if($this->type == 'file'): ?>
                <p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></p>
            <?php endif; ?>
            <?php if($this->type == 'image'): ?>
                <p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->image['address'], $this->template->element); ?></p>
            <?php endif; ?>
            <?php if($this->type == 'font'): ?>
                <p><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->font['address'], $this->template->element); ?></p>
            <?php endif; ?>
        </legend>

        <?php if($this->type == 'file'): ?>
            <form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">

                <p class="label"><?php echo JText::_('COM_TEMPLATES_TOGGLE_FULL_SCREEN'); ?></p>
                <div class="clr"></div>
                <div class="editor-border">
                    <?php echo $this->form->getInput('source'); ?>
                </div>
                <input type="hidden" name="task" value="" />
                <?php echo JHtml::_('form.token'); ?>


                <?php echo $this->form->getInput('extension_id'); ?>
                <?php echo $this->form->getInput('filename'); ?>
            </form>
        <?php endif; ?>
        <?php if($this->type == 'image'): ?>
            <form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
                <fieldset class="adminform">
                    <img id="image-crop" src="<?php echo $this->image['address'] . '?' . time(); ?>" />
                    <input type ="hidden" id="x" name="x" />
                    <input type ="hidden" id="y" name="y" />
                    <input type ="hidden" id="h" name="h" />
                    <input type ="hidden" id="w" name="w" />
                    <input type="hidden" name="task" value="" />
                    <?php echo JHtml::_('form.token'); ?>
                </fieldset>
            </form>
        <?php endif; ?>
        <?php if($this->type == 'font'): ?>
            <div class="font-preview">
                <form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
                    <fieldset class="adminform">
                        <p class="lead">H1</p><h1>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h1>
                        <p class="lead">H2</p><h2>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h2>
                        <p class="lead">H3</p><h3>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h3>
                        <p class="lead">H4</p><h4>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h4>
                        <p class="lead">H5</p><h5>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h5>
                        <p class="lead">H6</p> <h6>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </h6>
                        <p class="lead">Bold</p><b>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </b>
                        <p class="lead">Italics</p><i>Quickly gaze at Joomla! views from HTML, CSS, JavaScript and XML </i>
                        <p class="lead">Unordered List</p>
                        <ul>
                            <li>Item</li>
                            <li>Item</li>
                            <li>Item<br />
                                <ul>
                                    <li>Item</li>
                                    <li>Item</li>
                                    <li>Item<br />
                                        <ul>
                                            <li>Item</li>
                                            <li>Item</li>
                                            <li>Item</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                        <p class="lead">Ordered List</p>
                        <ol>
                            <li>Item</li>
                            <li>Item</li>
                            <li>Item<br />
                                <ul>
                                    <li>Item</li>
                                    <li>Item</li>
                                    <li>Item<br />
                                        <ul>
                                            <li>Item</li>
                                            <li>Item</li>
                                            <li>Item</li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                        </ol>
                        <input type="hidden" name="task" value="" />
                        <?php echo JHtml::_('form.token'); ?>
                    </fieldset>
                </form>
            </div>
        <?php endif; ?>

    </fieldset>
    <div class="clr"></div>
    <input type="hidden" name="task" value="" />
</div>