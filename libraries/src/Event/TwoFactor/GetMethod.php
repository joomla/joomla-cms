<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\TwoFactor;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;

/**
 * Concrete Event class for the onUserTwofactorGetMethod event
 *
 * @since __DEPLOY_VERSION__
 */
class GetMethod extends AbstractImmutableEvent implements ResultAwareInterface
{
	use ResultAware;
	use ResultTypeObjectAware;

	/**
	 * Public constructor
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		parent::__construct('onUserTwofactorGetMethod', []);

		$this->resultIsNullable        = true;
		$this->resultAcceptableClasses = [
			MethodDescriptor::class,
		];
	}
}
