<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_GUIDEDTOURS_TOURS_LIST',
    'formURL'    => 'index.php?option=com_guidedtours&view=tours',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help4.x:Guided_Tours',
    'icon'       => 'icon-map-signs',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_guidedtours')) {
    $displayData['createURL'] = 'index.php?option=com_guidedtours&task=tour.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
