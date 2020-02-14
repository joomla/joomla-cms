<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<span class="parent-category-name">
	<?php $title = $this->escape($displayData['item']->parent_title); ?>
	<?php if ($displayData['params']->get('link_parent_category') && !empty($displayData['item']->parent_slug)) : ?>
		<?php $url = '<a href="' . Route::_(ContentHelperRoute::getCategoryRoute($displayData['item']->parent_slug)) . '" itemprop="genre">' . $title . '</a>'; ?>
		<?php echo Text::sprintf('COM_CONTENT_PARENT', $url); ?>
	<?php else : ?>
		<?php echo Text::sprintf('COM_CONTENT_PARENT', '<span itemprop="genre">' . $title . '</span>'); ?>
	<?php endif; ?>
</span>
