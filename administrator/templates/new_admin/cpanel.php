<?php
/**
* @version $Id: cpanel.php 2069 2006-01-29 16:56:48Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

global $document;

?>
<div style="padding: 10px 2px;">	
	<table class="adminform">
	<tr>
		<td width="55%" valign="top">
		   <?php echo $document->execRenderer('modules','icon'); ?>
		</td>
		<td width="45%" valign="top">
			<div style="width=100%;">
				<form action="index2.php" method="post" name="adminForm">
				
				<?php
				$tabs = new mosTabs(1);
				$tabs->startPane( 'modules-cpanel' );
				?>
				
				<?php echo $document->execRenderer('modules', 'cpanel', array('style' => 1)); ?>
				
				<?php
				$tabs->endPane();
				?>
				
				</form>
			</div>
		</td>
	</tr>
	</table>
</div>

<?php
readfile( JPATH_SITE .'/TODO.php' );
?>