<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_quickicon
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$html = JHtml::_('icons.buttons', $buttons);
?>
<?php if (!empty($html)): ?>
	<div class="cpanel"><?php echo $html;?></div>
<?php endif;?>
