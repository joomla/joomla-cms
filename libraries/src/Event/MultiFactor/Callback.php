<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\MultiFactor;

\defined('JPATH_PLATFORM') or die;

use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Concrete Event class for the onUserMultifactorCallback event
 *
 * @since __DEPLOY_VERSION__
 */
class Callback extends AbstractImmutableEvent
{
	/**
	 * Public constructor
	 *
	 * @param   string  $method  The MFA method name
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(string $method)
	{
		parent::__construct('onUserMultifactorCallback', ['method' => $method]);
	}

	/**
	 * Validate the value of the 'method' named parameter
	 *
	 * @param   string|null  $value  The value to validate
	 *
	 * @return  string
	 * @throws  DomainException
	 * @since   __DEPLOY_VERSION__
	 */
	public function setMethod(string $value): string
	{
		if (empty($value))
		{
			throw new DomainException(sprintf("Argument 'method' of event %s must be a non-empty string.", $this->name));
		}

		return $value;
	}
}
