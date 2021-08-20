<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<nav class="mod-breadcrumbs__wrapper" aria-label="<?php echo htmlspecialchars($module->title, ENT_QUOTES, 'UTF-8'); ?>">
	<ol itemscope itemtype="https://schema.org/BreadcrumbList" class="mod-breadcrumbs breadcrumb px-3 py-2">
		<?php if ($params->get('showHere', 1)) : ?>
			<li class="mod-breadcrumbs__here float-start">
				<?php echo Text::_('MOD_BREADCRUMBS_HERE'); ?>&#160;
			</li>
		<?php else : ?>
			<li class="mod-breadcrumbs__divider float-start">
				<span class="divider icon-location icon-fw" aria-hidden="true"></span>
			</li>
		<?php endif; ?>

		<?php
		// Get rid of duplicated entries on trail including home page when using multilanguage
		for ($i = 0; $i < $count; $i++)
		{
			if ($i === 1 && !empty($list[$i]->link) && !empty($list[$i - 1]->link) && $list[$i]->link === $list[$i - 1]->link)
			{
				unset($list[$i]);
			}
		}

		// Find last and penultimate items in breadcrumbs list
		end($list);
		$last_item_key   = key($list);
		prev($list);
		$penult_item_key = key($list);

		// Make a link if not the last item in the breadcrumbs
		$show_last = $params->get('showLast', 1);

		// Generate the trail
		foreach ($list as $key => $item) :
			if ($key !== $last_item_key) :
				if (!empty($item->link)) :
					$breadcrumbItem = '<a itemprop="item" href="' . Route::_($item->link) . '" class="pathway"><span itemprop="name">' . html_entity_decode($item->name, ENT_QUOTES, 'UTF-8') . '</span></a>';
				else :
					$breadcrumbItem = '<span itemprop="name">' . $item->name . '</span>';
				endif;
				// Render all but last item - along with separator ?>
				<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="mod-breadcrumbs__item breadcrumb-item"><?php echo $breadcrumbItem; ?>
					<meta itemprop="position" content="<?php echo $key + 1; ?>">
				</li>
			<?php elseif ($show_last) :
				$breadcrumbItem = '<span itemprop="name">' . html_entity_decode($item->name, ENT_QUOTES, 'UTF-8') . '</span>';
				// Render last item if required. ?>
				<li aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="mod-breadcrumbs__item breadcrumb-item active"><?php echo $breadcrumbItem; ?>
					<meta itemprop="position" content="<?php echo $key + 1; ?>">
				</li>
			<?php endif;
		endforeach; ?>
	</ol>
</nav>
