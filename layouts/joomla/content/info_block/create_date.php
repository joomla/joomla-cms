<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<dd class="create">
	<span class="info-icon icon-calendar icon-fw" aria-hidden="true"></span>
	<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_CREATED_DATE_ON', ''); ?>
	</span>
	<span class="info-value">
		<?php echo HTMLHelper::_('date', $displayData['item']->created, Text::_('DATE_FORMAT_LC3')); ?>
	</span>
</dd>
