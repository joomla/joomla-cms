<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$scriptStrings = [
  'COM_TEMPLATES_SELECT_OPTION_NONE',
  'COM_TEMPLATES_SELECT_TYPE_OF_OVERRIDE_LABEL',
  'COM_TEMPLATES_COMPONENT',
  'COM_TEMPLATES_COMPONENT_LABEL',
  'COM_TEMPLATES_PLUGIN',
  'COM_TEMPLATES_MODULE',
  'COM_TEMPLATES_LAYOUT_SELECT_LABEL',
  'COM_TEMPLATES_LAYOUT',
  'COM_TEMPLATES_CREATE_OVERRIDE',
  'COM_TEMPLATES_LAYOUT_CUSTOM_NAME',
  'COM_TEMPLATES_LAYOUT_CUSTOM_NAME_LABEL',
  'JYES',
  'JNO',
  'COM_TEMPLATES_CREATE_FORK_LABEL',
  'COM_TEMPLATES_CREATE_CHILD_LABEL',
  'COM_TEMPLATES_CREATE_NEW_TEMPLATE',
  'COM_TEMPLATES_CREATE_NEW_CHILD',
];

foreach ($scriptStrings as $c) {
  Text::script($c);
}
