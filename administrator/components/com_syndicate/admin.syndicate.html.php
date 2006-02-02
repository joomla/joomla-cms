<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Syndicate
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Syndicate
*/
class HTML_syndicate {

	function settings( $option, &$params, $id ) {
		mosCommonHTML::loadOverlib();
		?>
		<style type="text/css">
		table.paramlist td.paramlist_key {
			width: 150px;
			text-align: left;
			height: 30px;
		}		
		</style>
		
		<form action="index2.php" method="post" name="adminForm">
		
		<div id="editcell">				
			<table class="adminform">
			<thead>
			<tr>
				<th>
					<?php echo JText::_( 'Parameters' ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>
					&nbsp;
				</th>
			</tr>
			</tfoot>
			<tbody>
			<tr>
				<td>
					<?php
					echo $params->render();
					?>
				</td>
			</tr>
			</tbody>
			</table>
		</div>

		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<input type="hidden" name="name" value="Syndicate" />
		<input type="hidden" name="admin_menu_link" value="option=com_syndicate" />
		<input type="hidden" name="admin_menu_alt" value="Manage Syndication Settings" />
		<input type="hidden" name="option" value="com_syndicate" />
		<input type="hidden" name="admin_menu_img" value="js/ThemeOffice/component.png" />
		<input type="hidden" name="option" value="<?php echo $option; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		</form>
		<?php
	}
}
?>