<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::_('behavior.caption');
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
require_once JPATH_ROOT.'/components/com_cjblog/helpers/constants.php';

JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');
JLoader::register('CjBlogHelper', JPATH_BASE . '/components/com_cjblog/helpers/helper.php');

$params = JComponentHelper::getParams('com_cjblog');
$layout = $params->get('layout', 'default');
JFactory::getLanguage()->load('com_cjblog');
?>
<div id="cj-wrapper" class="profile-articles<?php echo $this->pageclass_sfx;?>">
	<?php 
	if(count($this->items) > 0)
	{
		echo JLayoutHelper::render(
			$layout.'.articles_list', 
			array('items'=>$this->items, 'params'=>$params, 'pagination'=>$this->pagination, 'heading'=>'', 'viewName'=>''),
			null,
			array('debug' => false, 'client' => 0, 'component' => 'com_cjblog'));
	}
	else 
	{
		?>
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');?></div>
		<?php
	}
	?>
</div>