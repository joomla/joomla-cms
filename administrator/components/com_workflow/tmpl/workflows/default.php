<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-10">
            <div id="j-main-container" class="j-main-container">
                <!--		    --><?php //echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->workflows)) : ?>
                    <div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
				<?php else : ?>
                    <table class="table table-striped" id="emailList">
                        <thead><?php echo $this->loadTemplate('head');?></thead>
                        <tbody class="js-draggable"><?php echo $this->loadTemplate('body');?></tbody>
                    </table>
				<?php endif; ?>
                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
            </div>
        </div>
    </div>
