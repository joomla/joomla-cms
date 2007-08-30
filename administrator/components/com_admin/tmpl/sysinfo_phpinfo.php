<?php
/**
 * @version		$Id$
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'PHP Information' ); ?></legend>
		<table class="adminform">
		<thead>
		<tr>
			<th colspan="2">
				&nbsp;
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th colspan="2">
				&nbsp;
			</th>
		</tr>
		</tfoot>
		<tbody>
		<tr>
			<td>
				<?php
				ob_start();
				phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
				$phpinfo = ob_get_contents();
				ob_end_clean();

				preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
				$output = preg_replace('#<table#', '<table class="adminlist" align="center"', $output[1][0]);
				$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
				$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
				$output = preg_replace('#<hr />#', '', $output);
				$output = str_replace('<div class="center">', '', $output);
				$output = str_replace('</div>', '', $output);

				echo $output;
				?>
			</td>
		</tr>
		</tbody>
		</table>
</fieldset>