<?php

/**
 * @version
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

	<?php echo JHtml::_('sliders.panel', JText::_('Contact_Links'), 'display-links'); ?>
<div class="jcontact-links">

	<ul>
		<?php if ($this->contact->params->get('linka')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linka') ?>"><?php echo $this->params->get('linka_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkb')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkb') ?>"><?php echo $this->params->get('linkb_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkc')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkc') ?>"><?php echo $this->params->get('linkc_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linkd')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linkd') ?>"><?php echo $this->params->get('linkd_name')  ?></a></li>
		<?php endif; ?>
		<?php if ($this->contact->params->get('linke')) : ?>
			<li><a href="<?php echo $this->contact->params->get('linke') ?>"><?php echo $this->params->get('linke_name')  ?></a></li>
		<?php endif; ?>
	</ul>
</div>

