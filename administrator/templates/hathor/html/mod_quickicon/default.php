<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$html = JHtml::_('icons.buttons', $buttons);
?>
<?php if (!empty($html)): ?>
	<div class="cpanel clearfix">
		<?php echo $html;?>
	</div>
<?php endif;?>
