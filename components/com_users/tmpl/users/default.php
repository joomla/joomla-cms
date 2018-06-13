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

<div>
	<?php foreach ($this->items as $item) : ?>
		<?php echo $item->event->afterDisplayTitle; ?>
		<div>
			<?php echo $item->event->beforeDisplayContent; ?>

			<a href="<?php echo Route::_(UsersHelperRoute::getUserRoute($item->slug, $item->group_id, $lang)); ?>" itemprop="url">
				<?php echo $item->name; ?>
			</a>
			<p> <?php echo $item->id; ?></p>
		</div>

		<?php echo $item->event->afterDisplayContent; ?>

	<?php endforeach; ?>
</div>

