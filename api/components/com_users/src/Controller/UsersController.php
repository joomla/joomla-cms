<?php
/**
 * @package     Joomla.API
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The users controller
 *
 * @since  4.0.0
 */
class UsersController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'users';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $default_view = 'users';

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		$data = (array) json_decode($this->input->json->getRaw(), true);

		foreach (FieldsHelper::getFields('com_users.user') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		$this->input->set('data', $data);

		return parent::save($recordKey);
	}

	/**
	 * User list view with filtering of data
	 *
	 * @return  static  A BaseController object to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidParameterException
	 */
	public function displayList()
	{
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$filter        = InputFilter::getInstance();

		if (array_key_exists('state', $apiFilterInfo))
		{
			$this->modelState->set('filter.state', $filter->clean($apiFilterInfo['state'], 'INT'));
		}

		if (array_key_exists('active', $apiFilterInfo))
		{
			$this->modelState->set('filter.active', $filter->clean($apiFilterInfo['active'], 'INT'));
		}

		if (array_key_exists('groupid', $apiFilterInfo))
		{
			$this->modelState->set('filter.group_id', $filter->clean($apiFilterInfo['groupid'], 'INT'));
		}

		if (array_key_exists('search', $apiFilterInfo))
		{
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (array_key_exists('registrationDateStart', $apiFilterInfo))
		{
			$registrationStartInput = $filter->clean($apiFilterInfo['registrationDateStart'], 'STRING');
			$registrationStartDate  = Date::createFromFormat(\DateTime::RFC3339, $registrationStartInput);

			if (!$registrationStartDate)
			{
				// Send the error response
				$error = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'registrationDateStart');

				throw new InvalidParameterException($error, 400, null, 'registrationDateStart');
			}

			$this->modelState->set('filter.registrationDateStart', $registrationStartDate);
		}

		if (array_key_exists('registrationDateEnd', $apiFilterInfo))
		{
			$registrationEndInput = $filter->clean($apiFilterInfo['registrationDateEnd'], 'STRING');
			$registrationEndDate  = Date::createFromFormat(\DateTime::RFC3339, $registrationEndInput);

			if (!$registrationEndDate)
			{
				// Send the error response
				$error = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'registrationDateEnd');
				throw new InvalidParameterException($error, 400, null, 'registrationDateEnd');
			}

			$this->modelState->set('filter.registrationDateEnd', $registrationEndDate);
		}
		elseif (array_key_exists('registrationDateStart', $apiFilterInfo)
			&& !array_key_exists('registrationDateEnd', $apiFilterInfo))
		{
			// If no end date specified the end date is now
			$this->modelState->set('filter.registrationDateEnd', new Date);
		}

		if (array_key_exists('lastVisitDateStart', $apiFilterInfo))
		{
			$lastVisitStartInput = $filter->clean($apiFilterInfo['lastVisitDateStart'], 'STRING');
			$lastVisitStartDate  = Date::createFromFormat(\DateTime::RFC3339, $lastVisitStartInput);

			if (!$lastVisitStartDate)
			{
				// Send the error response
				$error = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'lastVisitDateStart');
				throw new InvalidParameterException($error, 400, null, 'lastVisitDateStart');
			}

			$this->modelState->set('filter.lastVisitStart', $lastVisitStartDate);
		}

		if (array_key_exists('lastVisitDateEnd', $apiFilterInfo))
		{
			$lastVisitEndInput = $filter->clean($apiFilterInfo['lastVisitDateEnd'], 'STRING');
			$lastVisitEndDate  = Date::createFromFormat(\DateTime::RFC3339, $lastVisitEndInput);

			if (!$lastVisitEndDate)
			{
				// Send the error response
				$error = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'lastVisitDateEnd');

				throw new InvalidParameterException($error, 400, null, 'lastVisitDateEnd');
			}

			$this->modelState->set('filter.lastVisitEnd', $lastVisitEndDate);
		}
		elseif (array_key_exists('lastVisitDateStart', $apiFilterInfo)
			&& !array_key_exists('lastVisitDateEnd', $apiFilterInfo))
		{
			// If no end date specified the end date is now
			$this->modelState->set('filter.lastVisitEnd', new Date);
		}

		return parent::displayList();
	}
}
