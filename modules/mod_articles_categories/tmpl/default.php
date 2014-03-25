<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

//TODO This should be done differently, but can't find appropriate method in the core. Definitely needs refactoring.
$input = JFactory::getApplication()->input;
$view = $input->get('view'); 

if($view == 'category'){
	$catid = $input->getInt('id'); 
}
else if($view == 'article'){
	$catid = $input->getInt('catid'); 
}

$categories = JCategories::getInstance('Content')->get($catid);
$catpath = $categories->getPath();

?>
<ul class="categories-module<?php echo $moduleclass_sfx; ?>">
<?php
require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default').'_items');
?></ul>
