<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_quickicon
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
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
