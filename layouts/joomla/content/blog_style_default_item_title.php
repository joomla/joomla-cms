<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// Create a shortcut for params.
$params  = $displayData->params;
$canEdit = $displayData->params->get('access-edit');

HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');
?>

<?php if ($displayData->state == 0 || $params->get('show_title') || ($params->get('show_author') && !empty($displayData->author ))) : ?>
	<div class="page-header">
		<?php if ($params->get('show_title')) : ?>
			<h2 itemprop="name">
				<?php if ($params->get('link_titles') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) : ?>
					<a href="<?php echo Route::_(
						ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)
					); ?>" itemprop="url">
						<?php echo $this->escape($displayData->title); ?>
					</a>
				<?php else : ?>
					<?php echo $this->escape($displayData->title); ?>
				<?php endif; ?>
			</h2>
		<?php endif; ?>
		
		<?php if ($displayData->state == 0) : ?>
			<span class="badge badge-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
		<?php endif; ?>

		<?php if (strtotime($displayData->publish_up) > strtotime(Factory::getDate())) : ?>
			<span class="badge badge-warning"><?php echo Text::_('JNOTPUBLISHEDYET'); ?></span>
		<?php endif; ?>
		
		<?php if ($displayData->publish_down != Factory::getDbo()->getNullDate()
			&& (strtotime($displayData->publish_down) < strtotime(Factory::getDate()))
		) : ?>
			<span class="badge badge-warning"><?php echo Text::_('JEXPIRED'); ?></span>
		<?php endif; ?>
	</div>
<?php endif; ?>
