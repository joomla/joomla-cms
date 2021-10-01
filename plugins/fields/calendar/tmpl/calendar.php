<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$value = $field->value;

if ($value == '')
{
	return;
}

if (is_array($value))
{
	$value = implode(', ', $value);
}

$formatString =  $field->fieldparams->get('showtime', 0) ? 'DATE_FORMAT_LC5' : 'DATE_FORMAT_LC4';

echo htmlentities(HTMLHelper::_('date', $value, Text::_($formatString)));
