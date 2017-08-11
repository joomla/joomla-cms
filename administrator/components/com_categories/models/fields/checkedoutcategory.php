<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('checkedout');

/**
 * Form Field to load a list of users who checked items out.
 *
 * @since  __DEPLOY_VERSION__
 */
class CheckedoutCategoryField extends CheckedoutField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type = 'CheckedoutCategory';

	/**
	 * Builds the query for the checked out category list.
	 *
	 * @return  JDatabaseQuery  The query for the checked out category form field.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getQuery()
	{
		$query = parent::getQuery();

		// Add filtering by extension name.
		if (preg_match("%^com_categories\.categories\.([0-1a-z_]+)\.filter$%", $this->form->getName(), $matches))
		{
			$query->where($this->alias . '.extension = ' . $query->quote('com_' . $matches[1]));
		}

		return $query;
	}
}
