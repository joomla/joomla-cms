<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

$uploadLink = 'index.php?option=com_joomlaupdate&layout=upload';

$displayData = [
	'textPrefix' => 'COM_JOOMLAUPDATE',
	'formURL'    => 'index.php?option=com_joomlaupdate&view=joomlaupdate',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Updating_from_an_existing_version',
	'icon'       => 'icon-loop joomlaupdate',
	'createURL'  => 'index.php?option=com_joomlaupdate&task=update.purge&' . Session::getFormToken() . '=1'
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.admin', 'com_joomlaupdate'))
{
	$displayData['formAppend'] = '<div class="text-center">' . HTMLHelper::_('link', $uploadLink, Text::_('COM_JOOMLAUPDATE_EMPTYSTATE_APPEND')) . '</div>';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);

?>
