<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Check if we need to show the page title.
if ($this->params->get('show_page_title', 1)):
	?>
	<h2><?php echo $this->params->get('page_title'); ?></h2>
	<?php
endif;
?>
<?php echo $this->loadTemplate('core'); ?>

<?php echo $this->loadTemplate('custom'); ?>

<a href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&member_id='.$this->data->id);?>">
	<?php echo JText::_('Users_Edit_Profile'); ?></a>
