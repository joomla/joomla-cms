<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
// TODO Retrieve the microdata global configuration
// TODO Retrieve the microdata scopeType of each article
?>
<ul class="latestnews<?php echo $moduleclass_sfx; ?>">
<?php foreach ($list as $item) :  ?>
	<li <?php echo JFactory::getMicrodata()->setType('Article')->displayScope(); ?>>
		<a <?php echo JFactory::getMicrodata()->property('url')->display(); ?> href="<?php echo $item->link; ?>">
			<?php echo JFactory::getMicrodata()->content($item->title)->property('name')->display(); ?></a>
	</li>
<?php endforeach; ?>
</ul>
