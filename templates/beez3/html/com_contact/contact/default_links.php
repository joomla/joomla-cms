<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($this->params->get('presentation_style') === 'sliders') : ?>
	<?php echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_LINKS'), 'display-links'); ?>
<?php endif; ?>
<?php if ($this->params->get('presentation_style') === 'tabs') : ?>
	<?php echo JHtmlTabs::panel(JText::_('COM_CONTACT_LINKS'), 'display-links'); ?>
<?php endif; ?>
<?php if  ($this->params->get('presentation_style') === 'plain'):?>
	<?php echo '<h3>'. JText::_('COM_CONTACT_LINKS').'</h3>'; ?>
<?php endif; ?>

<div class="contact-links">
	<ul class="nav nav-list">
		<?php foreach (range('a', 'e') as $char) :// letters 'a' to 'e'
			$link = $this->contact->params->get('link'.$char);
			$label = $this->contact->params->get('link'.$char.'_name');

			if (!$link) :
				continue;
			endif;

			// Add 'http://' if not present
			$link = (0 === strpos($link, 'http')) ? $link : 'http://'.$link;

			// If no label is present, take the link
			$label = $label ?: $link;
			?>
			<li>
				<a href="<?php echo $link; ?>">
				    <?php echo $label; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
