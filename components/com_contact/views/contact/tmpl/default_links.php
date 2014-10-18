<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($this->params->get('presentation_style') == 'sliders')
{
	echo JHtml::_('bootstrap.addSlide', 'slide-contact', JText::_('COM_CONTACT_LINKS'), 'display-links');
}

if ($this->params->get('presentation_style') == 'tabs')
{
	echo JHtml::_('bootstrap.addTab', 'myTab', 'display-links', JText::_('COM_CONTACT_LINKS', true));
}

if ($this->params->get('presentation_style') == 'plain')
{
	echo '<h3>' . JText::_('COM_CONTACT_LINKS') . '</h3>';
}
?>
<div class="contact-links">
	<ul class="nav nav-tabs nav-stacked">
	<?php
		// Letters 'a' to 'e'
		foreach (range('a', 'e') as $char)
		{
			$link = $this->contact->params->get('link' . $char);
			$label = $this->contact->params->get('link' . $char . '_name');

			if (!$link)
			{
				continue;
			}

			// Add 'http://' if not present
			$link = (0 === strpos($link, 'http')) ? $link : 'http://' . $link;

			// If no label is present, take the link
			$label = ($label) ? $label : $link;
			?>
				<li>
					<a href="<?php echo $link; ?>" itemprop="url">
						<?php echo $label; ?>
					</a>
				</li>
			<?php
		}
	?>
	</ul>
</div>
<?php
if ($this->params->get('presentation_style') == 'sliders')
{
	echo JHtml::_('bootstrap.endSlide');
}

if ($this->params->get('presentation_style') == 'tabs')
{
	echo JHtml::_('bootstrap.endTab');
}

