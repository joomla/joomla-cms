<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$positions = $this->model->getPositions();

echo JHtml::_('select.genericlist', $positions, 'jform[position]', '', '', '', $this->item['position']);
