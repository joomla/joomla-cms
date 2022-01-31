<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logsupport
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

if ($params->get('automatic_title'))
{
	$module->title = Text::_('MOD_LOGINSUPPORT_TITLE');
}

require ModuleHelper::getLayoutPath('mod_loginsupport', $params->get('layout', 'default'));
