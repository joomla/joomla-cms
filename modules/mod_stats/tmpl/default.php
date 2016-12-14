<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<dl class="stats-module<?php echo $moduleclass_sfx ?>" <?php if ($params->get('modulecss_id')) : ?> id="<?php echo $params->get('module_id'); ?>"<?php endif;?>>
<?php foreach ($list as $item) : ?>
	<dt><?php echo $item->title;?></dt>
	<dd><?php echo $item->data;?></dd>
<?php endforeach; ?>
</dl>
