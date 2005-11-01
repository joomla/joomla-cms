<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Restricted access' );

?>
<table class="adminform">
<tr>
	<td width="55%" valign="top">
	   <?php mosLoadAdminModules( 'icon', 0 ); ?>
	</td>
	<td width="45%" valign="top">
		<div style="width=100%;">
			<form action="index2.php" method="post" name="adminForm">
			<?php
			$tabs = new mosTabs(1);
			$tabs->startPane( 'modules-cpanel' );
			?>
			<?php mosLoadAdminModules( 'cpanel', 1 ); ?>
			<?php
			$tabs->endPane();
			?>
			</form>
		</div>
	</td>
</tr>
</table>

<style type="text/css">
#notes { text-align: center; margin: auto 0; }

s { color: red; }
.todo {
	background-color: #F9F9F9;
	height: 300px;
	overflow: auto;
	color: black;
	border: 1px solid #999999;
	padding: 20px;
	display: block;
	text-align: left;
}
hr { border: 1px dotted black; }
span.todotitle {
	font-weight: bold;
	color: black;
}
</style>
<div id="notes">
<h2>TESTER NOTES</h2>
<pre class="todo">
	<?php
	readfile( $GLOBALS['mosConfig_absolute_path'].'/TODO.php' );
	?>
</pre>
</div>