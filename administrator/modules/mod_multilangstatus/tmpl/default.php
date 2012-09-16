<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_multilangstatus
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.modal');
?>
<div class="btn-group multilanguage"><a class="modal" href="<?php echo JRoute::_('index.php?option=com_languages&view=multilangstatus&tmpl=component');?>" rel="{handler:'iframe', size:{x:700,y:300}}"><i class="icon-comment"></i> <?php echo JText::_('MOD_MULTILANGSTATUS');?></a></div>
