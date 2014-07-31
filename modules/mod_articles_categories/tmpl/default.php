<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_categories
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<ul class="categories-module<?php echo $moduleclass_sfx; ?>">
<?php
require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default').'_items');
?></ul>
