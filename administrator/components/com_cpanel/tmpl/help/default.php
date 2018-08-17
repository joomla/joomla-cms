<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="com-cpanel-help">
	<h2 class="com-cpanel-help__header">
		<span class="fa fa-info-circle" aria-hidden="true"></span>
		<?php echo Text::_('MOD_MENU_HELP'); ?>
	</h2>
	<?php foreach ($this->links as $link) : ?>
		<a class="list-group-item list-group-item-action" href="<?php echo $link['link']; ?>"><?php echo Text::_($link['label']); ?></a>
	<?php endforeach; ?>
</div>