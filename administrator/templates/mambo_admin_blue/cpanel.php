<?php
/**
* @version $Id: cpanel.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

?>
<div id="datacellcpanel">
<table class="adminform">
<tr>
	<td valign="top">
		<?php mosLoadAdminModules( 'icon', 0 ); ?>
	</td>
	<td width="47%" valign="top">
		<div style="width=100%;">
			<form action="index2.php" method="post" name="adminForm">
				<?php mosLoadAdminModules( 'cpanel', 1 ); ?>
			</form>
		</div>
	</td>
</tr>
</table>
</div>
<?php
global $_VERSION;

if ( $_VERSION->DEV_STATUS == 'Dev' ) {
	// DEV ONLY
	?>
	<style type="text/css">
	s {
		color: red;
	}
	.todo {
		background-color: #E9EFF5;
		text-align: left;
		width: 60%;
		height: 300px;
		overflow: auto;
		color: blue;
		border: 1px solid #999999;
		padding: 20px;
	}
	hr {
		border: 1px dotted black;
	}
	span.todotitle {
		font-weight: bold;
		color: black;
	}
	</style>
	<strong>
		TESTER NOTES
	</strong>
	<br/>
	<pre class="todo">
		<?php
		readfile( $GLOBALS['mosConfig_absolute_path'].'/TODO.php' );
		?>
	</pre>
	<?php
}
?>