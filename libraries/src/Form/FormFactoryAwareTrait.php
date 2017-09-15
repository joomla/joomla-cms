<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

defined('_JEXEC') or die;

/**
 * Defines the trait for a FormFactoryInterface Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait FormFactoryAwareTrait
{
	/**
	 * FormFactoryInterface
	 *
	 * @var    FormFactoryInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $formFactory;

	/**
	 * Get the FormFactoryInterface.
	 *
	 * @return  FormFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException May be thrown if the FormFactory has not been set.
	 */
	public function getFormFactory()
	{
		if ($this->formFactory)
		{
			return $this->formFactory;
		}

		throw new \UnexpectedValueException('FormFactory not set in ' . __CLASS__);
	}

	/**
	 * Set the form factory to use.
	 *
	 * @param   FormFactoryInterface  $formFactory  The form factory to use.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setFormFactory(FormFactoryInterface $formFactory = null)
	{
		$this->formFactory = $formFactory;

		return $this;
	}
}
