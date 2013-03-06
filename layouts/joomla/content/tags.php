<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
<div class="tags">
		<?php if (!empty($displayData))
		{
			JLoader::register('TagsHelperRoute', JPATH_BASE.'/components/com_tags/helpers/route.php');
			foreach ($displayData as $i=>$tag)
			{
				if (in_array($tag->access, JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'))))
				{
					echo '<span class="tag-' . $tag->tag_id . ' tag-list' . $i . ' "><a href="' . JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id)).'" >' . $this->escape($tag->title) . '</a></span>&nbsp;' . ' ' ;
				}
			}
		} ?>
</div>
