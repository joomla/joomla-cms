<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_newsflash
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
?>
<table class="moduletable<?php echo $params->get('moduleclass_sfx') ?>">
	<tr>
	<?php foreach ($list as $item) : ?>
		<td>
			<?php modNewsFlashHelper::renderItem($item, $params, $access); ?>
		</td>
	<?php endforeach; ?>
	</tr>
</table>