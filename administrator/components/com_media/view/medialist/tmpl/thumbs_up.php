<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
		<li class="span2">
			<article class="thumbnail center" >
				<div class="height-100">
					<div class="imgBorder">
						<a class="btn btn-link btn-large" href="index.php?option=com_media&amp;controller=media.display.medialist&amp;view=medialist&amp;tmpl=component&amp;folder=<?php echo $this->state->get('parent'); ?>" target="folderframe">
							<i class="icon-arrow-up"></i></a>
					</div>
					<div class="controls">
						<span>&#160;</span>
					</div>
					<div>
						<a href="index.php?option=com_media&amp;controller=media.display.medialist&amp;view=medialist&amp;tmpl=component&amp;folder=<?php echo $this->state->get('parent'); ?>" target="folderframe">..</a>
					</div>					
				</div>
			</article>
		</li>
