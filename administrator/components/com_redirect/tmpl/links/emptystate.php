<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_REDIRECT',
	'formURL'    => 'index.php?option=com_redirect&view=links',
	'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Redirects:_Links',
	'icon'       => 'icon-map-signs redirect',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_redirect'))
{
	$displayData['createURL'] = 'index.php?option=com_redirect&task=link.add';
}

if ($user->authorise('core.create', 'com_redirect')
	&& $user->authorise('core.edit', 'com_redirect')
	&& $user->authorise('core.edit.state', 'com_redirect'))
{
	$displayData['formAppend'] = HTMLHelper::_(
		'bootstrap.renderModal',
		'collapseModal',
		[
			'title' => Text::_('COM_REDIRECT_BATCH_OPTIONS'),
			'footer' => $this->loadTemplate('batch_footer'),
		],
		$this->loadTemplate('batch_body')
	);
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
