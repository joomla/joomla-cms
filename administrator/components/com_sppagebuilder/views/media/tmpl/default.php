<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined('_JEXEC') or die ('restricted aceess');

JHtml::_('formbehavior.chosen', '.sp-pagebuilder-media-toolbar select');

$doc = JFactory::getDocument();
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/pbfont.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/font-awesome.min.css' );
$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/sppagebuilder.css' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/media.js' );
$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/utilities.js' );

JText::script('COM_SPPAGEBUILDER_MEDIA_MANAGER_CONFIRM_DELETE');
JText::script('COM_SPPAGEBUILDER_MEDIA_MANAGER_ENTER_DIRECTORY_NAME');
?>

<div class="sp-pagebuilder-admin-top"></div>

<div id="sp-pagebuilder-media-manager" class="sp-pagebuilder-admin<?php echo (count((array) $this->items)) ? '': ' sp-pagebuilder-media-manager-empty'; ?> clearfix" style="position: relative;">
	<div id="j-sidebar-container" class="span2">
		<?php echo JLayoutHelper::render('brand'); ?>
		<?php echo $this->sidebar; ?>

		<ul id="sp-pagebuilder-media-types">
			<?php echo JLayoutHelper::render('media.categories', array( 'categories'=>$this->categories )); ?>
		</ul>
	</div>

	<div id="j-main-container" class="span10">
		<div class="sp-pagebuilder-main-container-inner">

			<div class="sp-pagebuilder-media-toolbar clearfix">

				<div id="sp-pagebuilder-media-tools" class="pull-left clearfix">
					<div>
						<input type="file" id="sp-pagebuilder-media-input-file" multiple="multiple" style="display:none">
						<a href="#" id="sp-pagebuilder-upload-media" class="sp-pagebuilder-btn sp-pagebuilder-btn-success"><i class="fa fa-upload"></i><span class="hidden-phone hidden-xs"> <?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_UPLOAD_FILES'); ?></span></a>
					</div>

					<div style="display: none;">
						<a href="#" id="sp-pagebuilder-cancel-media" class="sp-pagebuilder-btn sp-pagebuilder-btn-default"><i class="fa fa-times"></i> <?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_CANCEL'); ?></a>
					</div>

					<div style="display: none;">
						<a href="#" id="sp-pagebuilder-media-create-folder" class="sp-pagebuilder-btn sp-pagebuilder-btn-primary"><i class="fa fa-plus"></i> <?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_CREATE_FOLDER'); ?></a>
					</div>

					<div>
						<div class="sp-pagebuilder-media-search">
							<i class="fa fa-search"></i>
							<input type="text" class="sp-pagebuilder-form-control" id="sp-pagebuilder-media-search-input" placeholder="<?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_SEARCH'); ?>">
							<a href="#" class="sp-pagebuilder-clear-search" style="display: none;"><i class="fa fa-times-circle"></i></a>
						</div>
					</div>
				</div>

				<div class="pull-right hidden-phone">
					<div>
						<select id="sp-pagebuilder-media-filter" data-type="browse">
							<option value=""><?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_MEDIA_ALL'); ?></option>
							<?php foreach ($this->filters as $key => $this->filter) { ?>
								<option value="<?php echo $this->filter->year . '-' . $this->filter->month; ?>"><?php echo JHtml::_('date', $this->filter->year . '-' . $this->filter->month, 'F Y'); ?></option>
								<?php } ?>
							</select>
						</div>

						<div style="display: none;">
							<a href="#" id="sp-pagebuilder-delete-media" class="sp-pagebuilder-btn sp-pagebuilder-btn-danger hidden-phone hidden-xs"><i class="fa fa-minus-circle"></i> <?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_DELETE'); ?></a>
						</div>
					</div>
				</div><!--/.page-builder-pages-toolbar-->

				<div class="sp-pagebuilder-media-list clearfix">
					<div class="sp-pagebuilder-media-empty">
						<div>
							<i class="fa fa-upload"></i>
							<h3 class="sp-pagebuilder-media-empty-title">
								<?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_DRAG_DROP_UPLOAD'); ?>
							</h3>
							<div>
								<a href="#" id="sp-pagebuilder-upload-media-empty" class="sp-pagebuilder-btn sp-pagebuilder-btn-primary sp-pagebuilder-btn-lg"><?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_OR_SELECT'); ?></a>
							</div>
						</div>
					</div>
					<div class="sp-pagebuilder-media-wrapper">
						<ul class="sp-pagebuilder-media clearfix">
							<?php
							foreach ($this->items as $key => $this->item) {
								echo  JLayoutHelper::render('media.format', array('media'=>$this->item, 'support'=>'all'));
							}
							?>
						</ul>
						<?php if($this->total > ($this->limit + $this->start)) { ?>
							<div class="sp-pagebuilder-media-loadmore clearfix">
								<a id="sp-pagebuilder-media-loadmore" class="sp-pagebuilder-btn sp-pagebuilder-btn-primary sp-pagebuilder-btn-lg" href="#"><i class="fa fa-refresh"></i> <?php echo JText::_('COM_SPPAGEBUILDER_MEDIA_MANAGER_LOAD_MORE'); ?></a>
							</div>
							<?php } ?>
						</div>
					</div>

					<div class="clearfix"></div>
					<?php echo JLayoutHelper::render('footer'); ?>

				</div>
			</div>
		</div>
