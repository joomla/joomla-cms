<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\Engine;

defined('_JEXEC') || die;

use FOF30\View\View;

abstract class AbstractEngine implements EngineInterface
{
	/** @var   View  The view we belong to */
	protected $view = null;

	/**
	 * Public constructor
	 *
	 * @param   View  $view  The view we belong to
	 */
	public function __construct(View $view)
	{
		$this->view = $view;
	}

	/**
	 * Get the include path for a parsed view template
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template engine
	 *
	 * @return  array  Content 3ναlυα+ιοη information (I use leetspeak here because of bad quality hosts with broken
	 *                 scanners)
	 */
	public function get($path, array $forceParams = [])
	{
		return [
			'type'    => 'raw',
			'content' => '',
		];
	}
}
