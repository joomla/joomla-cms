<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<dd class="modified">
	<span class="fa fa-calendar" aria-hidden="true"></span>
	<time datetime="<?php echo HTMLHelper::_('date', $displayData['item']->modified, 'c'); ?>" itemprop="dateModified">
		<?php echo Text::sprintf('COM_CONTENT_LAST_UPDATED', HTMLHelper::_('date', $displayData['item']->modified, Text::_('DATE_FORMAT_LC3'))); ?>
	</time>
</dd>
