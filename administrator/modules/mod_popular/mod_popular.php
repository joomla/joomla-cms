<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$query = "SELECT a.hits, a.id, a.sectionid, a.title, a.created, u.name"
. "\n FROM #__content AS a"
. "\n LEFT JOIN #__users AS u ON u.id=a.created_by"
. "\n WHERE a.state <> -2"
. "\n ORDER BY hits DESC"
. "\n LIMIT 10"
;
$database->setQuery( $query );
$rows = $database->loadObjectList();
?>

<table class="adminlist">
<tr>
	<td class="title">
		<strong><?php echo JText::_( 'Most Popular Items' ); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_( 'Created' ); ?></strong>
	</td>
	<td class="title">
		<strong><?php echo JText::_( 'Hits' ); ?></strong>
	</td>
</tr>
<?php
foreach ($rows as $row) {
	if ( $row->sectionid == 0 ) {
		$link = 'index2.php?option=com_typedcontent&amp;task=edit&amp;hidemainmenu=1&amp;id='. $row->id;
	} else {
		$link = 'index2.php?option=com_content&amp;task=edit&amp;hidemainmenu=1&amp;id='. $row->id;
	}
	?>
	<tr>
		<td>
			<a href="<?php echo $link; ?>">
				<?php echo htmlspecialchars($row->title, ENT_QUOTES);?></a>
		</td>
		<td>
			<?php echo $row->created;?>
		</td>
		<td>
			<?php echo $row->hits;?>
		</td>
	</tr>
	<?php
}
?>
</table>