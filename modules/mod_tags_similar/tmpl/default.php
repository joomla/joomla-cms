<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Helper\RouteHelper;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');
?>

<div class="mod-tagssimilar tagssimilar">
<?php if ($list) : ?>
	<ul>
	<?php foreach ($list as $i => $item) : ?>
		<li>
			<?php if (($item->type_alias === 'com_users.category') || ($item->type_alias === 'com_banners.category')) : ?>
				<?php if (!empty($item->core_title)) :
					echo htmlspecialchars($item->core_title, ENT_COMPAT, 'UTF-8');
				endif; ?>
			<?php else : ?>
				<?php $item->route = new RouteHelper; ?>
				<a href="<?php echo Route::_(TagsHelperRoute::getItemRoute($item->content_item_id, $item->core_alias, $item->core_catid, $item->core_language, $item->type_alias, $item->router)); ?>">
					<?php if (!empty($item->core_title)) :
						echo htmlspecialchars($item->core_title, ENT_COMPAT, 'UTF-8');
					endif; ?>
				</a>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<span class="mod-tagssimilar__message"><?php echo Text::_('MOD_TAGS_SIMILAR_NO_MATCHING_TAGS'); ?></span>
<?php endif; ?>
</div>
