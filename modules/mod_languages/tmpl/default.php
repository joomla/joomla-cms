<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_languages
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('stylesheet', 'mod_languages/template.css', array(), true);
?>
<div class="mod-languages<?php echo $moduleclass_sfx ?>">
<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>
	<ul class="<?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block';?>">
<?php foreach($list as $language):?>
	<?php if ($params->get('show_active', 0) || !$language->active):?>
		<li class="<?php echo $language->active ? 'lang-active' : '';?>">
		<a href="<?php echo $language->link;?>">
		<?php if ($params->get('image', 1)):?>
			<?php echo JHtml::_('image', 'mod_languages/'.$language->image.'.gif', $language->title, array('title'=>$language->title), true);?>
		<?php else:?>
			<?php echo $language->title;?>
		<?php endif;?>
				</a>
			</li>
	<?php endif;?>
<?php endforeach;?>
	</ul>
<?php if ($footerText) : ?>
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
