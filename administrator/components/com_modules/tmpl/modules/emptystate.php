<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_MODULES',
	'formURL'    => 'index.php?option=com_modules&view=select&client_id=' . $this->clientId,
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Module',
	'icon'       => 'icon-cube module',
	// Although it is (almost) impossible to get to this page with no created Administrator Modules, we add this for completeness.
	'title'      => Text::_('COM_MODULES_EMPTYSTATE_TITLE_' . ($this->clientId ? 'ADMINISTRATOR' : 'SITE')),
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', 'com_modules'))
{
	$displayData['createURL'] = 'index.php?option=com_modules&view=select&client_id=' . $this->clientId;
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
