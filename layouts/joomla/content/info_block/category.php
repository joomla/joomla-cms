<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

?>
<dd class="category | category-name">
	<?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'info-icon icon-folder-open icon-fw']); ?>
	<?php $title = $this->escape($displayData['item']->category_title); ?>
	<?php if ($displayData['params']->get('link_category') && !empty($displayData['item']->catid)) : ?>
		<?php $url = '<a class="info-value" href="' . Route::_(
			RouteHelper::getCategoryRoute($displayData['item']->catid, $displayData['item']->category_language)
			)
			. '" itemprop="genre">' . $title . '</a>'; ?>
		<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_CATEGORY', ''); ?>	
		</span>
		<?php echo $url; ?>
	<?php else : ?>
		<span class="info-label">
		<?php echo Text::sprintf('COM_CONTENT_CATEGORY', ''); ?>	
		</span>
		<span class="info-value" itemprop="genre"><?php echo $title; ?></span>
	<?php endif; ?>
</dd>
