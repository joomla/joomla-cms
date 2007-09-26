<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$db =& JFactory::getDBO();
$query = 'SELECT menutype, COUNT(id) AS numitems'
. ' FROM #__menu'
. ' WHERE published = 1'
. ' GROUP BY menutype'
;
$db->setQuery( $query );
$rows = $db->loadObjectList();
?>
<table class="adminlist">
	<tr>
		<td class="title" width="80%">
			<strong><?php echo JText::_( 'Menu' ); ?></strong>
		</td>
		<td class="title">
			<strong><?php echo JText::_( 'Num Items' ); ?></strong>
		</td>
	</tr>
<?php
foreach ($rows as $row)
{
	$link = 'index.php?option=com_menus&amp;task=view&amp;menutype='. $row->menutype;
	?>
	<tr>
		<td>
			<a href="<?php echo $link; ?>">
				<?php echo $row->menutype;?></a>
		</td>
		<td>
			<?php echo $row->numitems;?>
		</td>
	</tr>
<?php
}
?>
</table>
