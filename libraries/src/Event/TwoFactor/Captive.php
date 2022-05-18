<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\TwoFactor;

\defined('JPATH_PLATFORM') or die;

use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\Table\TfaTable;

/**
 * Concrete Event class for the onUserTwofactorCaptive event
 *
 * @since __DEPLOY_VERSION__
 */
class Captive extends AbstractImmutableEvent implements ResultAwareInterface
{
	use ResultAware;
	use ResultTypeObjectAware;

	/**
	 * Public constructor
	 *
	 * @param   TfaTable  $record  The TFA record to use in the captive login page
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(TfaTable $record)
	{
		parent::__construct('onUserTwofactorCaptive', ['record' => $record]);

		$this->resultIsNullable        = true;
		$this->resultAcceptableClasses = [
			CaptiveRenderOptions::class,
		];
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
}
