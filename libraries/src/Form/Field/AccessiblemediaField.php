<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Field\SubformField;

/**
 * The Field to load the form inside current form
 *
 * @since  __DEPLOY_VERSION__
 */
class AccessiblemediaField extends SubformField
{
	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		parent::setup($element, $value, $group);

		$file = __DIR__ . '/accessiblemedia/accessiblemedia.xml';

		$this->formsource = Path::clean($file);
		$this->layout = 'joomla.form.field.media.accessiblemedia';

		return true;
	}
}
