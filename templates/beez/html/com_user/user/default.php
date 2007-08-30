<?php // no direct access
defined('_JEXEC') or die('Restricted access');

// temporary fix
$hlevel = 2;
$ptlevel = 1;

echo '<h' . $ptlevel . ' class="componentheading">';
echo JText::_( 'Welcome!' );
echo '</h' . $ptlevel . '>';

echo '<div class="contentdescription">';
echo JText::_( 'WELCOME_DESC' );
echo '</div>';
?>