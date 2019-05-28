<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<dd class="createdby">
	<?php $author = ($displayData['item']->created_by_alias ?: $displayData['item']->author); ?>
	<?php $author = '<span>' . $author . '</span>'; ?>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', HTMLHelper::_('link', $displayData['item']->contact_link, $author)); ?>
	<?php else : ?>
		<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
	<?php endif; ?>
</dd>
