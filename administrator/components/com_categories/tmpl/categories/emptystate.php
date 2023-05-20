<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$extension = $this->state->get('filter.extension');
$component = $this->state->get('filter.component');
$section = $this->state->get('filter.section');

// Special handling for the title as com_categories is a service component for many other components. Copied from the categories view.
$lang = Factory::getApplication()->getLanguage();
$lang->load($component, JPATH_BASE)
|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

// If a component categories title string is present, let's use it.
if ($lang->hasKey($component_title_key = strtoupper($component . ($section ? "_$section" : '')) . '_CATEGORIES_TITLE')) {
    $title = Text::_($component_title_key);
} elseif ($lang->hasKey($component_section_key = strtoupper($component . ($section ? "_$section" : '')))) {
    // Else if the component section string exists, let's use it
    $title = Text::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', $this->escape(Text::_($component_section_key)));
} else // Else use the base title
{
    $title = Text::_('COM_CATEGORIES_CATEGORIES_BASE_TITLE');
}

$displayData = [
    'textPrefix' => 'COM_CATEGORIES',
    'formURL'    => 'index.php?option=com_categories&extension=' . $extension,
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Category',
    'title'      => $title,
    'icon'       => 'icon-folder categories content-categories',
];

if (Factory::getApplication()->getIdentity()->authorise('core.create', $extension)) {
    $displayData['createURL'] = 'index.php?option=com_categories&extension=' . $extension . '&task=category.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
