<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_toolbar
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$toolbar = JToolbar::getInstance('toolbar')->render('toolbar');

require JModuleHelper::getLayoutPath('mod_toolbar', $params->get('layout', 'default'));
