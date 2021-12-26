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
<dd class="createdby" itemprop="author" itemscope itemtype="https://schema.org/Person">
	<span class="info-icon icon-user icon-fw" aria-hidden="true"></span>
	<?php $author = ($displayData['item']->created_by_alias ?: $displayData['item']->author); ?>
	<?php $author = '<span class="info-value" itemprop="name">' . $author . '</span>'; ?>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', ''); ?>
		</span>
		<?php echo HTMLHelper::_('link', $displayData['item']->contact_link, $author, array('class' => 'info-value', 'itemprop' => 'url')); ?>
	<?php else : ?>
		<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', ''); ?>
		</span>
		<?php echo $author; ?>
	<?php endif; ?>
</dd>
