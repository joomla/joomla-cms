<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="newsflash<?php echo $moduleclass_sfx; ?>"<?php if ($params->get('modulecss_id')) : ?> id="<?php echo $params->get('module_id'); ?>"<?php endif;?>>
	<?php foreach ($list as $item) : ?>
		<?php require JModuleHelper::getLayoutPath('mod_articles_news', '_item'); ?>
	<?php endforeach; ?>
</div>
