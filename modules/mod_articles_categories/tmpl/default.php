<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="categories-module<?php echo $moduleclass_sfx; ?> mod-list">
<?php require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default') . '_items'); ?>
</ul>
