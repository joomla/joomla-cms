<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<article class="thumbnail center">
	<div class="height-120" style="height: 120px;">
		<div class="imgTotal">
			<div class="imgBorder">
				<a class="btn" href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" target="folderframe">
					<span class="icon-arrow-up"></span></a>
			</div>
		</div>

		<div class="controls">
			<span>&#160;</span>
		</div>

		<div class="imginfoBorder">
			<a href="index.php?option=com_media&amp;view=mediaList&amp;tmpl=component&amp;folder=<?php echo $this->state->parent; ?>" target="folderframe">..</a>
		</div>
	</div>
</article>
