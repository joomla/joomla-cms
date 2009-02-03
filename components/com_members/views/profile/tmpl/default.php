<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

// Check if we need to show the page title.
if ($this->params->get('show_page_title', 1)):
	?>
	<h1><?php echo $this->params->get('page_title'); ?></h1>
	<?php
endif;
?>
<?php echo $this->loadTemplate('core'); ?>

<?php echo $this->loadTemplate('custom'); ?>

<a href="<?php JRoute::_('index.php?option=com_members&task=profile.edit&member_id='.$this->data->id);?>">
	<?php echo JText::_('Members_Edit_Profile'); ?></a>
