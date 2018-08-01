<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;

\JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');
$lang  = Factory::getLanguage();
?>

<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
	</div>
<?php endif; ?>

<?php if ($this->params->get('show_group_title', 1)) : ?>
	<h2>
		<span class="subheading-users"><?php echo $this->group->title; ?></span>
	</h2>
<?php endif; ?>

<?php foreach ($this->items as $item) : ?>
	<div class="user-item" itemscope itemtype="https://schema.org/Person">
		<div class="user-item-content"><!-- Double divs required for IE11 grid fallback -->
			<div class="item-content">
				<h2 itemprop="name">
					<a href="<?php echo Route::_(UsersHelperRoute::getUserRoute($item->slug, $item->group_id, $lang)); ?>" itemprop="url">
						<?php echo $this->escape($item->name); ?>
					</a>
				</h2>
				<?php echo $item->event->afterDisplayTitle; ?>
				<?php echo $item->event->beforeDisplayContent; ?>
				<?php echo $item->event->afterDisplayContent; ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>
