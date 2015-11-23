<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$theme 				= $this->params->get('theme', 'default');
$avatar  			= $this->params->get('avatar_component', 'cjforum');
$profileComponent 	= $this->params->get('profile_component', 'cjforum');
$layout 			= $this->params->get('layout', 'default');
$rowClass 			= $this->params->get('layout', 'default') != 'bs3' ? 'row-fluid' : 'row';

$api = new CjLibApi();
?>
<div id="cj-wrapper" class="search<?php echo $this->pageclass_sfx;?>">
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>null));?>

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1 class="page-header no-space-top"> <?php echo $this->escape($this->heading); ?> </h1>
	<?php endif; ?>
	
	<div class="<?php echo $rowClass;?>">
		<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span12' : 'col-lg-12 col-md-12 col-sm-12';?>">
			<div class="<?php echo $rowClass;?>">
				<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span12' : 'col-lg-12 col-md-12 col-sm-12';?>">
					<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('COM_CJFORUM_ENTER_SEARCH_CRITERIA');?></div>
				</div>
			</div>
			
			<form action="<?php echo JRoute::_('index.php?option=com_cjforum');?>" method="post">
				<div class="<?php echo $rowClass;?>">
					<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span12' : 'col-lg-12 col-md-12 col-sm-12';?>">
						<div class="panel panel-<?php echo $theme;?>">
							<div class="panel-heading">
								<div class="panel-title"><?php echo JText::_('COM_CJFORUM_LABEL_KEYWORDS');?></div>
							</div>
							<div class="panel-body">
							 	<input name="list_filter" type="text" class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span8' : 'col-lg-8 col-md-8 col-sm-12';?>" 
							 		placeholder="<?php echo JText::_('COM_CJFORUM_LABEL_KEYWORDS');?>">
							 	<select name="list_filter_field" class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span4' : 'col-lg-4 col-md-4 col-sm-12';?>">
							 		<option value="title"><?php echo JText::_('COM_CJFORUM_SEARCH_TITLES');?></option>
							 		<option value="author"><?php echo JText::_('COM_CJFORUM_SEARCH_USER_NAME');?></option>
							 		<option value="createdby"><?php echo JText::_('COM_CJFORUM_SEARCH_USERID');?></option>
							 	</select>
							 	<label class="checkbox"><input type="checkbox" value="1" name="filter_all_keywords"> <?php echo JText::_('COM_CJFORUM_SEARCH_ALL_WORDS');?></label>
							</div>
						</div>
					</div>
				</div>
				
				<?php /** ?>
				<div class="<?php echo $rowClass;?>">
					<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span12' : 'col-lg-12 col-md-12 col-sm-12';?>">
						<div class="panel panel-<?php echo $theme;?>">
							<div class="panel-heading">
								<div class="panel-title"><?php echo JText::_('JTAG');?></div>
							</div>
							<div class="panel-body">
								<p><?php echo JText::_('COM_CJFORUM_SEARCH_IN_TAGS');?></p>
								<?php $tagLayout = new JLayoutFile('joomla.content.tags'); ?>
								<?php echo JHtml::_('tag.ajaxfield', '#tags', false); ?>
							</div>
						</div>
					</div>
				</div>
				<?php **/?>
				
				<div class="<?php echo $rowClass;?>">
					<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span6' : 'col-lg-6 col-md-6 col-sm-6';?>">
						<div class="panel panel-<?php echo $theme;?>">
							<div class="panel-heading">
								<div class="panel-title"><?php echo JText::_('COM_CJFORUM_SEARCH_OPTIONS');?></div>
							</div>
							<div class="panel-body">
								<label><?php echo JText::_('COM_CJFORUM_SEARCH_ORDER_BY');?></label>
								<select name="filter_order" size="1">
									<option value="a.created"><?php echo JText::_('COM_CJFORUM_LABEL_DATE');?></option>
									<option value="a.replies"><?php echo JText::_('COM_CJFORUM_LABEL_REPLIES');?></option>
									<option value="a.catid"><?php echo JText::_('COM_CJFORUM_LABEL_CATEGORY');?></option>
								</select>
								<label><?php echo JText::_('COM_CJFORUM_SEARCH_ORDER');?></label>
								<select name="filter_order_Dir" size="1">
									<option value="asc"><?php echo JText::_('COM_CJFORUM_ASCENDING');?></option>
									<option value="desc"><?php echo JText::_('COM_CJFORUM_DESCENDING');?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span6' : 'col-lg-6 col-md-6 col-sm-6';?>">
						<div class="panel panel-<?php echo $theme;?>">
							<div class="panel-heading">
								<div class="panel-title"><?php echo JText::_('COM_CJFORUM_LABEL_CATEGORIES');?></div>
							</div>
							<div class="panel-body">
								<?php 
								$categories = JHtml::_('category.categories', 'com_cjforum');
								foreach ($categories as $id=>$category)
								{
									if($category->value == '1') 
									{
										unset($categories[$id]);
									}
								}
								
								$nocat = new JObject();
								$nocat->set('text', JText::_('COM_CJFORUM_LABEL_ALL_CATEGORIES'));
								$nocat->set('value', '0');
								$nocat->set('disable', false);
								
								array_unshift($categories, $nocat);
								echo JHTML::_('select.genericlist', $categories, 'catid[]', 'size = "6" multiple="multiple"');
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="well">
					<div class="<?php echo $rowClass;?>">
						<div class="center">
							<a href="<?php echo JRoute::_('index.php?option=com_cjforum')?>" class="btn btn-default"><?php echo JText::_('JCANCEL');?></a>
							<button class="btn btn-primary" type="submit"><i class="fa fa-search-plus"></i> <?php echo JText::_('COM_CJFORUM_LABEL_SEARCH');?></button>
						</div>
					</div>
				</div>
				
				<input type="hidden" name="view" value="topics">
				<input type="hidden" id="filter_featured" name="filter_featured" value="">
			</form>
		</div>
	</div>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
</div>