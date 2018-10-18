<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<div class="com-mailto-send p-2">
	<div class="com-mailto-send__close text-right">
		<a href="javascript: void window.close()">
			<?php echo Text::_('COM_MAILTO_CLOSE_WINDOW'); ?> <?php echo HTMLHelper::_('image', 'mailto/close-x.png', null, null, true); ?>
		</a>
	</div>
	<h2 class="com-mailto-send__message">
		<?php echo Text::_('COM_MAILTO_EMAIL_SENT'); ?>
	</h2>
</div>
