<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$displayData = array(
		'textPrefix' => 'COM_PRIVACY_CONSENTS',
		'formURL'    => 'index.php?option=com_privacy&view=consents',
		'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Help40:Privacy:_Consents',
);

echo \Joomla\CMS\Layout\LayoutHelper::render('joomla.content.blankstate', $displayData);
