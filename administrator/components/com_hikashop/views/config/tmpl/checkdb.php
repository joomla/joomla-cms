<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
<?php
foreach($this->results as $result) {
	if(is_string($result)) {
		echo '<p style="padding:0;margin:0 0 5px;">' . $result . '</p>';
		continue;
	}

	list($type, $msg) = $result;

	echo '<p style="padding:0;margin:0 0 5px;">';

	switch($type) {
		case 'error_msg':
			if(HIKASHOP_BACK_RESPONSIVE)
				echo '<span class="label label-warning">'.$msg.'</span>';
			else
				echo '<span style="background-color:#f89406;padding:2px 4px;color:white;font-weight: bold;">'.$msg.'</span>';
			$msg = '';
			break;
		case 'error':
		case 'err':
			if(HIKASHOP_BACK_RESPONSIVE)
				echo '<span class="label label-important">Error</span> ';
			else
				echo '<span style="background-color:#b94a48;padding:2px 4px;color:white;font-weight: bold;">Error</span> ';
			break;
		case 'success':
			if(HIKASHOP_BACK_RESPONSIVE)
				echo '<span class="label label-success">OK</span> ';
			else
				echo '<span style="background-color:#468847;padding:2px 4px;color:white;font-weight: bold;">OK</span> ';
			break;
		case 'info':
		default:
			if(HIKASHOP_BACK_RESPONSIVE)
				echo '<span class="label label-info">Info</span> ';
			else
				echo '<span style="background-color:#3a87ad;padding:2px 4px;color:white;font-weight: bold;">Info</span> ';
			break;
	}

	echo $msg;

	echo '</p>';
}
?>
</div>
