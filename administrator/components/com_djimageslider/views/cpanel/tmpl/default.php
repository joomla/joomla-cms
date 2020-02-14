<?php 
/**
 * @version $Id$
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */


defined('_JEXEC') or die('Restricted access'); ?>

<?php if (version_compare(JVERSION, '3.0', '>=')) { ?>

<div class="<?php echo $this->classes->row ?>">

<?php if(!empty( $this->sidebar)): ?>
<div id="j-sidebar-container" class="<?php echo $this->classes->col ?>2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="<?php echo $this->classes->col ?>10">
<?php else: ?>
<div id="j-main-container">
<?php endif;?>
	
	<div class="<?php echo $this->classes->row ?>">
		<div class="cpanel-left <?php echo $this->classes->col ?>8">
			<div class="cpanel">
			
				<h3><?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_CPANEL') ?></h3>
			
				<div class="icon">
					<a href="index.php?option=com_categories&extension=com_djimageslider">
						<img src="components/com_djimageslider/assets/icon-48-category.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_CATEGORIES') ?>" />
						<span><?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_CATEGORIES'); ?></span>
					</a>
				</div>
					
				<div class="icon">
					<a href="index.php?option=com_djimageslider&view=items">
						<img src="components/com_djimageslider/assets/icon-48-slides.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_SLIDES') ?>" />
						<span><?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_SLIDES'); ?></span>
					</a>
				</div>
				
				<div class="icon">
					<a href="index.php?option=com_categories&view=category&layout=edit&extension=com_djimageslider">
						<img src="components/com_djimageslider/assets/icon-48-category-add.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_NEW_CATEGORY') ?>" />
						<span><?php echo JText::_('COM_DJIMAGESLIDER_NEW_CATEGORY'); ?></span>
					</a>
				</div>

				<div class="icon">
					<a href="index.php?option=com_djimageslider&view=item&layout=edit">
						<img src="components/com_djimageslider/assets/icon-48-slide-add.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_NEW_SLIDE') ?>" />
						<span><?php echo JText::_('COM_DJIMAGESLIDER_NEW_SLIDE'); ?></span>
					</a>
				</div>
				
				<div class="icon">
					<a href="https://dj-extensions.com/extensions/dj-image-slider.html" target="_blank">
						<img src="components/com_djimageslider/assets/icon-48-help.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_DOCUMENTATION') ?>" />
						<span><?php echo JText::_('COM_DJIMAGESLIDER_DOCUMENTATION'); ?></span>
					</a>
				</div>
			</div>
		</div>
			
		<div class="cpanel-right <?php echo $this->classes->col ?>4">
			<div class="cpanel well">
				<div class="<?php echo $this->classes->row ?>">
					<iframe src="https://dj-extensions.com/index.php?option=com_content&view=article&tmpl=component&id=437" style="border:0; width: 100%; max-width: 450px; height: 370px; margin: -10px 0; padding: 0;"></iframe>
				</div>
			</div>
		</div>

	</div>
</div>
</div>

<?php } else { ?>

<table class="adminform">
	<tr>
		<td width="55%" valign="top">
			<div class="cpanel-left">
				<div id="cpanel">
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_categories&extension=com_djimageslider">
								<img src="components/com_djimageslider/assets/icon-48-category.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_CATEGORIES') ?>" />
								<span><?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_CATEGORIES'); ?></span>
							</a>
						</div>
					</div>
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_djimageslider&view=items">
								<img src="components/com_djimageslider/assets/icon-48-slides.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_SLIDES') ?>" />
								<span><?php echo JText::_('COM_DJIMAGESLIDER_SUBMENU_SLIDES'); ?></span>
							</a>
						</div>
					</div>
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_categories&view=category&layout=edit&extension=com_djimageslider">
								<img src="components/com_djimageslider/assets/icon-48-category-add.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_NEW_CATEGORY') ?>" />
								<span><?php echo JText::_('COM_DJIMAGESLIDER_NEW_CATEGORY'); ?></span>
							</a>
						</div>
					</div>
					<div style="float:left;">
						<div class="icon">
							<a href="index.php?option=com_djimageslider&view=item&layout=edit">
								<img src="components/com_djimageslider/assets/icon-48-slide-add.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_NEW_SLIDE') ?>" />
								<span><?php echo JText::_('COM_DJIMAGESLIDER_NEW_SLIDE'); ?></span>
							</a>
						</div>
					</div>
					<div style="float:left;">
						<div class="icon">
							<a href="https://dj-extensions.com/extensions/dj-image-slider.html" target="_blank">
								<img src="components/com_djimageslider/assets/icon-48-help.png" alt="<?php echo JText::_('COM_DJIMAGESLIDER_DOCUMENTATION') ?>" />
								<span><?php echo JText::_('COM_DJIMAGESLIDER_DOCUMENTATION'); ?></span>
							</a>
						</div>
					</div>
			
				</div>
			</div>
			<div class="cpanel-right">
				<div class="cpanel">					
						<iframe src="https://dj-extensions.com/index.php?option=com_content&view=article&tmpl=component&id=437" style="border:0; width: 100%; max-width: 450px; height: 370px; margin: -10px 0; padding: 0;"></iframe>
					<div style="clear: both;" ></div>
				</div>
			</div>
		</td>
	</tr>
</table>
<?php } ?>

<div class="clr" style="clear: both"></div>
<?php echo DJIMAGESLIDERFOOTER; ?>