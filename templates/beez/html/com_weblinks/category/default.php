<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id$
 * @author Design & Accessible Team ( Angie Radtke / Robert Deutz )
 * @package Joomla
 * @subpackage Accessible-Template-Beez
 * @copyright Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
	$templateParams = new JParameter($content);
} else {
	$templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$hlevel = $templateParams->get('headerLevelComponent', '2');
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');

if ($this->params->get('page_title')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->category->name;
	echo '</h' . $ptlevel . '>';
}
echo '<div class="weblinks' . $this->params->get('pageclass_sfx') . '">';

if (@ $this->_models->weblinksmodelcategory->_category->image || @ $this->category->description) {
	echo '<div  class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	$wrap = '';
	/* we use the model data, the normal object has only the complete img-tag */
	$image = $this->_models[weblinksmodelcategory]->_category->image;
	$image_align = $this->_models[weblinksmodelcategory]->_category->image_position;

	if (isset ($image)) {
		$wrap = '<div class="wrap_image">&nbsp;</div>';
		echo '<img src="images/stories/' . $image . '" class="image_' . $image_align . '" />';
	}

	if ($this->params->get('description') && $this->category->description) {
		echo $this->category->description;
	}
	echo $wrap;
	echo '</div>';
}
echo $this->loadTemplate('items');
echo '</div>';
?>