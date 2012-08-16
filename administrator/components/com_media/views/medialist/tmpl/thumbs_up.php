<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
		<li class="imgOutline thumbnail height-80">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a class="btn" href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" target="folderframe">
						<i class="icon-arrow-up"></i></a>
				</div>
			</div>
			<div class="controls">
				<span>&#160;</span>
			</div>
			<div class="imginfoBorder">
				<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" target="folderframe">..</a>
			</div>
		</li>
