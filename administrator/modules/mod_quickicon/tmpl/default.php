<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$html = JHtml::_('links.linksgroups', ModQuickIconHelper::groupButtons($buttons));
?>
<?php if (!empty($html)) : ?>
	<div class="sidebar-nav quick-icons">
		<?php echo $html;?>
	</div>
<?php endif;?>
