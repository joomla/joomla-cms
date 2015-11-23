<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');

$params = JComponentHelper::getParams('com_communityanswers');
$layout = $params->get('layout', 'default');
JLoader::register('CommunityAnswersHelperRoute', JPATH_BASE . '/components/com_communityanswers/helpers/route.php');
JFactory::getLanguage()->load('com_communityanswers');
?>
<div id="cj-wrapper" class="profile-questions<?php echo $this->pageclass_sfx;?>">
	<?php 
	if(count($this->items) > 0)
	{
		echo JLayoutHelper::render(
			$layout.'.question_list', 
			array('items'=>$this->items, 'params'=>$params, 'pagination'=>$this->pagination, 'heading'=>'', 'viewName'=>''),
			null,
			array('debug' => false, 'client' => 0, 'component' => 'com_communityanswers'));
	}
	else 
	{
		?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');?></div>
		<?php
	}
	?>
</div>