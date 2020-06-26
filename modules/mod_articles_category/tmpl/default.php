<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

if (!$list)
{
	return;
}

?>
<ul class="mod-articlescategory category-module mod-list">
	<?php if ($grouped) : ?>
		<?php foreach ($list as $groupName => $items) : ?>
		<li>
			<div class="mod-articles-category-group"><?php echo Text::_($groupName); ?></div>
			<ul>
				<?php require ModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default') . '_items'); ?>
			</ul>
		</li>
		<?php endforeach; ?>
	<?php else : ?>
		<?php $items = $list; ?>
		<?php require ModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default') . '_items'); ?>
	<?php endif; ?>
</ul>
