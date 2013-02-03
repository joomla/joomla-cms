<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_multilanguagestatus
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::_('behavior.modal');
?>
<span class="multilanguage"><a class="modal" href="<?php echo JRoute::_('index.php?option=com_languages&view=multilangstatus&tmpl=component');?>" rel="{handler:'iframe', size:{x:700,y:300}}"><?php echo JText::_('MOD_MULTILANGSTATUS');?></a></span>
