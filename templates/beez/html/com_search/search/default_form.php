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

if (!count($this->results)) {
	echo JText :: _('help_text');
}

echo '<form action="index.php#content" method="get" class="search_result' . $this->params->get('pageclass_sfx') . '">';
echo '<a name="form1"></a>';

if (count($this->results)) {
	$level = $hlevel +1;
	echo '<h' . $level . '>';
	echo JText :: _('search_again');
	echo '</h' . $level . '>';
}

echo '<fieldset class="word">';
echo '<label for="search_searchword">' . JText :: _('Search Keyword') . '</label>';
echo '<input type="text" name="searchword" id="search_searchword"  maxlength="20" value="' . $this->searchword . '" class="inputbox" />';
echo '</fieldset>';

echo '<fieldset class="phrase">';
echo '<legend>' . JText :: _('Search Parameters') . '</legend>';
echo $this->lists['searchphrase'];
echo '<br /><br />';
echo '<label for="ordering" class="ordering">' . JText :: _('Ordering') . ':</label>';
echo $this->lists['ordering'];
echo '</fieldset>';

if ($this->params->get('search_areas', 1)) {
	echo '<fieldset class="only"><legend>' . JText :: _('Search Only') . ':' . '</legend>';
	foreach ($this->searchareas['search'] as $val => $txt) {
		$checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="true"' : '';
		echo '<input type="checkbox" name="areas[]" value="' . $val . '" id="area_' . $val . '" ' . $checked . ' />';
		echo '<label for="area_' . $val . '">';
		echo $txt;
		echo '</label>';
	}
	echo '</fieldset>';
}
echo '<p><input type="submit" name="submit" value="' . JText :: _('Search') . '" class="button" /></p>';
echo '<input type="hidden" name="option" value="com_search" />';
echo '<input type="hidden" name="Itemid" value="' . $Itemid . '" />';
echo '</form>';
?>