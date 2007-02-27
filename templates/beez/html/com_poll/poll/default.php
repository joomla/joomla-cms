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

?>
<script type = "text/javascript">
<!--
        var link = document.createElement('link');
        link.setAttribute('href', 'components/com_poll/assets/poll_bars.css');
        link.setAttribute('rel', 'stylesheet');
        link.setAttribute('type', 'text/css');
        var head = document.getElementsByTagName('head').item(0);
        head.appendChild(link);
//-->
</script>

<?php

if ($this->params->get( 'title'))
{
	echo '<h'.$ptlevel .' class="componentheading'. $this->params->get( 'pageclass_sfx' ).'">';
	echo $this->poll->title ? $this->poll->title : JText::_('Select Poll') ;  
	echo '</h'.$ptlevel .'>';
} else 	{
	echo '<h'.$ptlevel .' class="componentheading'. $this->params->get( 'pageclass_sfx' ).'">';
	echo JText::_('Poll') ;  
	echo '</h'.$ptlevel .'>';
}	

echo '<div class="poll'.$this->params->get( 'pageclass_sfx' ).'">';
echo '<form action="index.php" method="post" name="poll" id="poll">';
echo '<label for="id">';
echo JText::_('Select Poll') .'&nbsp;'; 
echo $this->lists['polls'];
echo '</label>';
echo '</form>';

if (count($this->votes))
{
	echo $this->loadTemplate('graph'); 
}
echo '</div>';
?>