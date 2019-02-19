<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Factory;

/**
 * MVCFactory aware trait.
 *
 * @since  __DEPLOY_VERSION__
 */
trait MVCFactoryAwareTrait
{
	/**
	 * The mvc factory.
	 *
	 * @var    MVCFactoryInterfaceCF
	 * @since  __DEPLOY_VERSION__
	 */
	private $mvcFactory;

	/**
	 * Returns the MVC factory.
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException
	 */
	protected function getMVCFactory(): MVCFactoryInterface
	{
		if ($this->mvcFactory)
		{
			return $this->mvcFactory;
		}

		throw new \UnexpectedValueException('MVC Factory not set in ' . __CLASS__);
	}

	/**
	 * Set the MVC factory.
	 *
	 * @param   MVCFactoryInterface  $mvcFactory  The MVC factory
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setMVCFactory(MVCFactoryInterface $mvcFactory)
	{
		$this->mvcFactory = $mvcFactory;
	}
}
