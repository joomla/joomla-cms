<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory\Magic;

defined('_JEXEC') || die;

use FOF30\Container\Container;

abstract class BaseFactory
{
	/**
	 * @var   Container|null  The container where this factory belongs to
	 */
	protected $container = null;

	/**
	 * Section used to build the namespace prefix.
	 *
	 * @var   string
	 */
	protected $section = 'auto';

	/**
	 * Public constructor
	 *
	 * @param   Container  $container  The container we belong to
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @return string
	 */
	public function getSection()
	{
		return $this->section;
	}

	/**
	 * @param   string  $section
	 */
	public function setSection($section)
	{
		$this->section = $section;
	}
}
