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
<?php if ($this->state->get('folder') != '') : ?>
		<li class="span2">
			<article class="thumbnail center" >
				<div>
					<div class="imgBorder height-40">
						<a class="btn btn-link btn-large" href="index.php?option=com_media&amp;controller=media.display.medialist&amp;view=medialist&amp;tmpl=component&amp;folder=<?php echo $this->state->get('parent'); ?>" target="folderframe">
							<i class="icon-arrow-up"></i></a>
					</div>
					<div class="height-40">
						<span>&#160;</span>
					</div>
					<div class="height-40">
						<a href="index.php?option=com_media&amp;controller=media.display.medialist&amp;view=medialist&amp;tmpl=component&amp;folder=<?php echo $this->state->get('parent'); ?>" target="folderframe">...</a>
					</div>					
				</div>
			</article>
		</li>
<?php endif; ?>
