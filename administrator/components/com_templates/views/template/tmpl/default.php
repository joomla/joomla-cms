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
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$canDo = TemplatesHelper::getActions();
$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.folder ul').hide();
		$('.show > ul').show();
		$('.folder-url').click(function(event){
			event.preventDefault();
		});
        $('.file').bind('click',function(e){
            e.stopPropagation();
        });
		$('.folder').bind('click',function(e){
			$(this).children('ul').toggle();
            e.stopPropagation();
		});

        $('#fileModal .folder-url').bind('click',function(e){
            $('.folder-url').removeClass('selected');
            e.stopPropagation();
            $('#fileModal input.address').val($(this).attr('data-id'));
            $(this).addClass('selected');
        });

        $('#folderModal .folder-url').bind('click',function(e){
            $('.folder-url').removeClass('selected');
            e.stopPropagation();
            $('#folderModal input.address').val($(this).attr('data-id'));
            $(this).addClass('selected');
        });

    });
</script>
<style>
    .selected{
        background: #08c;
        color: #fff;
    }
    .selected:hover{
        background: #08c !important;
        color: #fff;
    }
    .modal-body .column {
        width: 50%; float: left;
    }
    #deleteFolder{
        margin: 0;
    }
</style>
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'editor')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'editor', JText::_('Editor', true)); ?>
		<div class="row-fluid">
			<div class="span3">
				<?php $this->listDirectoryTree($this->files);?>
			</div>
			<div class="span9 thumbnail">
				<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
					<fieldset class="adminform">
						<legend><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></legend>
						<div class="clr"></div>
						<div class="editor-border">
						<?php echo $this->form->getInput('source'); ?>
						</div>
						<input type="hidden" name="task" value="" />
						<?php echo JHtml::_('form.token'); ?>
					</fieldset>
				
					<?php echo $this->form->getInput('extension_id'); ?>
					<?php echo $this->form->getInput('filename'); ?>
				</form>
			</div>
		</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'overrides', JText::_('Create Overrides', true)); ?>
        <div class="row-fluid">
            <div class="span6">
                <legend><?php echo JText::_('Modules');?></legend>
                <ul class="nav nav-list">
                    <?php foreach($this->overridesList['modules'] as $module): ?>
                        <li><a href="
                            <?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $module->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>
                        "><i class="icon-copy"></i>&nbsp;<?php echo $module->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="span6">
                <legend><?php echo JText::_('Components');?></legend>
                <ul class="nav nav-list">
                    <?php foreach($this->overridesList['components'] as $component): ?>
                        <li><a href="
                            <?php echo JRoute::_('index.php?option=com_templates&view=template&task=template.overrides&folder=' . $component->path . '&id=' . $input->getInt('id') . '&file=' . $this->file); ?>
                        "><i class="icon-copy"></i>&nbsp;<?php echo $component->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'less', JText::_('LESS Parameters', true)); ?>

    <?php echo JHtml::_('bootstrap.endTab'); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'preview', JText::_('Template Preview', true)); ?>

    <?php echo JHtml::_('bootstrap.endTab'); ?>
<?php echo JHtml::_('bootstrap.endTabSet'); ?>

<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
			method="post" name="adminForm" id="adminForm">
	<div  id="collapseModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY');?></h3>
		</div>
		<div class="modal-body">
			<div id="template-manager-css" class="form-horizontal">
				<div class="control-group">
					<label for="new_name" class="control-label hasTooltip" title="<?php echo JHtml::tooltipText('COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_LABEL')?></label>
					<div class="controls">
						<input class="input-xlarge" type="text" id="new_name" name="new_name"  />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal">Close</a>
			<button class="btn btn-primary" type="submit"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY'); ?></button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>
<div  id="deleteModal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_('Are you sure?');?></h3>
    </div>
    <div class="modal-body">
        <p>The file <?php echo $this->fileName; ?> will be deleted.</p>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
        <a href="<?php echo JRoute::_('index.php?option=com_templates&task=template.delete&id=' . $input->getInt('id') . '&file=' . $this->file); ?>" class="btn btn-danger">Delete</a>
    </div>
</div>
<div  id="fileModal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_('Create or Upload a new file.');?></h3>
    </div>
    <div class="modal-body">
        <div class="column">
            <form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
                class="well" >
                <fieldset>
                    <label>File Type</label>
                    <select name="type" required >
                        <option>- Select a file type -</option>
                        <option value="css">css</option>
                        <option value="php">php</option>
                        <option value="js">js</option>
                        <option value="xml">xml</option>
                        <option value="ini">ini</option>
                        <option value="less">less</option>
                    </select>
                    <label>File Name</label>
                    <input type="text" name="name" required />
                    <input type="hidden" class="address" name="address" />

                    <input type="submit" value="Create" class="btn btn-primary" />
                </fieldset>
            </form>
            <form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.uploadFile&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
                class="well" enctype="multipart/form-data" >
                <fieldset>
                    <input type="hidden" class="address" name="address" />
                    <input type="file" name="files" required />
                    <input type="submit" value="Upload" class="btn btn-primary" />
                </fieldset>
            </form>
        </div>
        <div class="column">
            <?php $this->listFolderTree($this->files);?>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Close</a>
    </div>
</div>
<div  id="folderModal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3><?php echo JText::_('Manage Folders');?></h3>
    </div>
    <div class="modal-body">
        <div class="column">
            <form method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.createFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>"
                  class="well" >
                <fieldset>
                    <label>Folder Name</label>
                    <input type="text" name="name" required />
                    <input type="hidden" class="address" name="address" />

                    <input type="submit" value="Create" class="btn btn-primary" />
                </fieldset>
            </form>
        </div>
        <div class="column">
            <?php $this->listFolderTree($this->files);?>
        </div>
    </div>
    <div class="modal-footer">
        <form id="deleteFolder" method="post" action="<?php echo JRoute::_('index.php?option=com_templates&task=template.deleteFolder&id=' . $input->getInt('id') . '&file=' . $this->file); ?>">
            <fieldset>
                <a href="#" class="btn" data-dismiss="modal">Close</a>
                <input type="hidden" class="address" name="address" />
                <input type="submit" value="Delete" class="btn btn-danger" />
            </fieldset>
        </form>
    </div>
</div>