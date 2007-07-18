<?php
/**
 * @version		$Id$
 */
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'Configuration File' ); ?></legend>
		<table class="adminlist">
		<thead>
			<tr>
				<th width="300">
					<?php echo JText::_( 'Setting' ); ?>
				</th>
				<th>
					<?php echo JText::_( 'Value' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">
					&nbsp;
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
			<?php
			$cf = file( JPATH_CONFIGURATION . '/configuration.php' );
			$config_output = array();
			foreach ($cf as $k => $v) {
				if (eregi( 'var \$host', $v)) {
					$cf[$k] = 'var $host = \'xxxxxx\'';
				} else if (eregi( 'var \$user', $v)) {
					$cf[$k] = 'var $user = \'xxxxxx\'';
				} else if (eregi( 'var \$password', $v)) {
					$cf[$k] = 'var $password = \'xxxxxx\'';
				} else if (eregi( 'var \$db ', $v)) {
					$cf[$k] = 'var $db = \'xxxxxx\'';
				} else if (eregi( 'var \$ftp_user ', $v)) {
					$cf[$k] = 'var $ftp_user = \'xxxxxx\'';
				} else if (eregi( 'var \$ftp_pass ', $v)) {
					$cf[$k] = 'var $ftp_pass = \'xxxxxx\'';
				} else if (eregi( '<?php', $v)) {
					$cf[$k] = '';
				} else if (eregi( '\?>', $v)) {
					$cf[$k] = '';
				} else if (eregi( '}', $v)) {
					$cf[$k] = '';
				} else if (eregi( 'class JConfig {', $v)) {
					$cf[$k] = '';
				}
				$cf[$k] 	= str_replace('var ','',$cf[$k]);
				$cf[$k] 	= str_replace(';','',$cf[$k]);
				$cf[$k] 	= str_replace(' = ','</td><td>',$cf[$k]);
				$cf[$k] 	= '<td>'. $cf[$k] .'</td>';
				if ($cf[$k] != '<td></td>') {
					$config_output[] 	= $cf[$k];
				}
			}
			echo implode( '</tr><tr>', $config_output );
			?>
			</tr>
		</tbody>
		</table>
</fieldset>