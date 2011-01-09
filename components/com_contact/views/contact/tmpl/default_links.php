<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php if ($this->params->get('presentation_style')!='plain'){?>
	<?php echo JHtml::_($this->params->get('presentation_style').'.panel', JText::_('COM_CONTACT_LINKS'), 'display-links'); }?>
<?php if ($this->params->get('presentation_style')=='plain'){?>
	<?php echo '<h3>'.JText::_('COM_CONTACT_LINKS').'</h3>'; }?>

<div class="contact-links">

	<ul>
		<?php if ($this->contact->params->get('linka')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linka') ?>"><?php echo $this->contact->params->get('linka_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkb')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkb') ?>"><?php echo $this->contact->params->get('linkb_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkc')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkc') ?>"><?php echo $this->contact->params->get('linkc_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkd')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkd') ?>"><?php echo $this->contact->params->get('linkd_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linke')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linke') ?>"><?php echo $this->contact->params->get('linke_name')  ?></a></li>
		<?php endif; ?>
	</ul>
</div>

