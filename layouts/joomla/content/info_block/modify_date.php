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
<dd class="modified">
	<span class="info-icon icon-calendar icon-fw" aria-hidden="true"></span>
	<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_LAST_UPDATED', ''); ?>
	</span>
	<time class="info-value" datetime="<?php echo HTMLHelper::_('date', $displayData['item']->modified, 'c'); ?>" itemprop="dateModified">
		<?php echo HTMLHelper::_('date', $displayData['item']->modified, Text::_('DATE_FORMAT_LC3')); ?>
	</time>
</dd>
