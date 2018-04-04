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
<dd class="create">
	<span class="fa fa-calendar" aria-hidden="true"></span>
	<time datetime="<?php echo HTMLHelper::_('date', $displayData['item']->created, 'c'); ?>" itemprop="dateCreated">
		<?php echo Text::sprintf('COM_CONTENT_CREATED_DATE_ON', HTMLHelper::_('date', $displayData['item']->created, Text::_('DATE_FORMAT_LC3'))); ?>
	</time>
</dd>
