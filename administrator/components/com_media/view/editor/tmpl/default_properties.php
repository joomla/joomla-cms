<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

echo $this->form->getControlGroup('core_title');
echo $this->form->getControlGroup('core_alias');

echo $this->form->getControlGroup('core_type_alias'); // Hidden field

echo $this->form->getControlGroup('core_body');
echo $this->form->getControlGroup('core_catid');
echo $this->form->getControlGroup('tags');

echo $this->form->getControlGroup('core_created_time');
echo $this->form->getControlGroup('core_created_user_id');
echo $this->form->getControlGroup('core_created_by_alias');

echo $this->form->getControlGroup('core_modified_time');
echo $this->form->getControlGroup('core_modified_user_id');
