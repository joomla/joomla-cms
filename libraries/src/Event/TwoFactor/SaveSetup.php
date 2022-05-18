<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\TwoFactor;

\defined('JPATH_PLATFORM') or die;

use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;
use Joomla\Component\Users\Administrator\Table\TfaTable;
use Joomla\Input\Input;

/**
 * Concrete Event class for the onUserTwofactorSaveSetup event
 *
 * @since __DEPLOY_VERSION__
 */
class SaveSetup extends AbstractImmutableEvent implements ResultAwareInterface
{
	use ResultAware;
	use ResultTypeArrayAware;

	/**
	 * Public constructor
	 *
	 * @param   TfaTable  $record  The record to save into
	 * @param   Input     $input   The application input object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(TfaTable $record, Input $input)
	{
		parent::__construct(
			'onUserTwofactorSaveSetup',
			[
				'record' => $record,
				'input'  => $input,
			]
		);

		$this->resultIsNullable = true;
	}

	/**
	 * Validate the value of the 'record' named parameter
	 *
	 * @param   TfaTable  $value  The value to validate
	 *
	 * @return  TfaTable
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRecord(TfaTable $value): TfaTable
	{
		if (empty($value))
		{
			throw new DomainException(sprintf('Argument \'record\' of event %s must be a TfaTable object', $this->name));
		}

		return $value;
	}

	/**
	 * Validate the value of the 'record' named parameter
	 *
	 * @param   Input  $value  The value to validate
	 *
	 * @return  Input
	 * @since   __DEPLOY_VERSION__
	 */
	public function setInput(Input $value): Input
	{
		if (empty($value))
		{
			throw new DomainException(sprintf('Argument \'input\' of event %s must be an Input object', $this->name));
		}

		return $value;
	}
}
