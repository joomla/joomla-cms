<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_modules', 'mod_articles_news/template.css');

if (empty($list))
{
	return;
}

?>
<ul class="mod-articlesnews-horizontal newsflash-horiz mod-list">
	<?php foreach ($list as $item) : ?>
		<li itemscope itemtype="https://schema.org/Article">
			<?php require ModuleHelper::getLayoutPath('mod_articles_news', '_item'); ?>
		</li>
	<?php endforeach; ?>
</ul>
