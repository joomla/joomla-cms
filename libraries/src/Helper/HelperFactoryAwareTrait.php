<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('_JEXEC') or die;

/**
 * Defines the trait for a HelperFactory Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait HelperFactoryAwareTrait
{
	/**
	 * HelperFactory
	 *
	 * @var    HelperFactory
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $helperFactory;

	/**
	 * Get the HelperFactory.
	 *
	 * @return  HelperFactory
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  \UnexpectedValueException May be thrown if the HelperFactory has not been set.
	 */
	public function getHelperFactory(): HelperFactory
	{
		if ($this->helper)
		{
			return $this->helper;
		}

		throw new \UnexpectedValueException('HelperFactory not set in ' . __CLASS__);
	}

	/**
	 * Sets the helper factory to use.
	 *
	 * @param   HelperFactory  $helperFactory  The helper factory to use.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHelperFactory(HelperFactory $helperFactory)
	{
		$this->helper = $helperFactory;
	}
}
