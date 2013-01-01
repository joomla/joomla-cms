<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
<div class="tags">
		<?php if (!empty($displayData->itemTags))
		{
			foreach ($displayData->itemTags as $key=>$tag)
			{
				echo '<span class="tag-' . $key .'"><a href="index.php?option=com_tags&view=tag&id='. (int) $tag->tag_id .'" >' . $tag->title . ' </a></span>' ;
			}
		} ?>
</div>
