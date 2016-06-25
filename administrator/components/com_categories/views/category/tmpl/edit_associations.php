<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($this->item->id != 0 && $this->item->language != '*')
{
	echo JLayoutHelper::render('joomla.edit.associations', $this);
}
else
{
	echo '<div class="alert alert-info">' . JText::_('JGLOBAL_ASSOC_NOT_POSSIBLE') . '</div>';
}
