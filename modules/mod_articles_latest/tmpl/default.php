<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_articles_latest
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<ul class="latestnews<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) :  ?>
	<li class="latestnews<?php echo $params->get('moduleclass_sfx'); ?>">
		<a href="<?php echo $item->link; ?>" class="latestnews<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>