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

if ($this->params->get('page_title'))
{
        echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
        echo $this->params->get( 'header' );
        echo '</h' . $ptlevel . '>';
}


echo '<div class="contact'.$this->params->get( 'pageclass_sfx' ).'">';
if ( $this->contact->params->get( 'drop_down' ) && count( $this->contacts ) > 1)
{
	echo '<form method="post" name="selectForm" target="_top" id="selectForm">';
	echo JText::_( 'Select Contact' );
	echo '<br />';
	echo JHTMLSelect::genericList($this->contacts, 'contact_id', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $this->contact->id);
    echo '<input type="hidden" name="option" value="com_contact" />';
    echo '<input type="hidden" name="Itemid" value="'.$Itemid.' " />';
	echo '</form>';
}

if ( $this->contact->name && $this->contact->params->get( 'name' ) )
{
	echo $this->contact->name;
}

if ( $this->contact->con_position && $this->contact->params->get( 'position' ) )
{
	echo $this->contact->con_position;
}

if ( $this->contact->image && $this->contact->params->get( 'image' ) )
{
	echo '<div style="float: right;">';
	echo '<img src="images/stories/'. $this->contact->image.'" align="middle" alt="'. JText::_( 'Contact' ).'" />';
	echo '</div>';
}

echo $this->loadTemplate('address');

if ( $this->contact->params->get( 'vcard' ) )
{
	echo '<p>'.JText::_( 'Download information as a' ).'<a href="index2.php?option=com_contact&amp;task=vcard&amp;contact_id='.$this->contact->id.'&amp;format=raw" >';
	echo JText::_( 'VCard' );
	echo '</a></p>';
}

if ( $this->contact->params->get('email_form') )
{
        echo $this->loadTemplate('form');
}
echo '</div>';
?>