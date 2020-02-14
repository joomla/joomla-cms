<?php
/**
 * @package     Joomla.Cms
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

?>
<?php if (!empty($displayData)) : ?>
	<div class="tags">
	    <span><?php echo JText::_('HELIX_TAGS'); ?>: </span>
		<?php foreach ($displayData as $i => $tag) : ?>
			<?php if (in_array($tag->access, JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id')))) : ?>
				<?php $tagParams = new Registry($tag->params); ?>
				<?php $link_class = $tagParams->get('tag_link_class'); ?>
				<a href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)) ?>" class="<?php echo $link_class; ?>" rel="tag"><?php echo $this->escape($tag->title); ?></a><?php if($i != (count($displayData)-1)) echo ','; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
