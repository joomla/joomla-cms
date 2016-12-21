<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Text
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('text');

/**
 * Fields Text form field
 *
 * @since  3.7.0
 */
class JFormFieldFText extends JFormFieldText implements JFormDomfieldinterface
{

	public $type = 'FText';
}
