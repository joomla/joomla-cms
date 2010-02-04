<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<?php if ($this->params->get('show_page_title', 1)): ?>
	<h2>
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h2>
<?php endif; ?>

<?php echo $this->loadTemplate('core'); ?>

<?php echo $this->loadTemplate('custom'); ?>

<?php if (JFactory::getUser()->id == $this->data->id) {?>
<a href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&member_id='.(int) $this->data->id);?>">
	<?php echo JText::_('Users_Edit_Profile'); ?></a>
<?php  }	?>