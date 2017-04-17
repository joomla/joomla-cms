<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="adminlist table" cellpadding="1" width="100%">
	<tbody id="result">
	<?php
	$k = 0;
				for($i = 0,$a = count($this->list);$i<$a;$i++){
					$this->row =& $this->list[$i];
					$this->k=$k;
					include(dirname(__FILE__).DS.'child.php');
					$k = 1-$k;
				}
	?>
	</tbody>
</table>
