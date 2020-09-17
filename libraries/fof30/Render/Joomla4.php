<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Render;

defined('_JEXEC') || die;

use FOF30\Container\Container;

/**
 * Renderer class for use with Joomla! 4.x
 *
 * Renderer options
 *
 * wrapper_id              The ID of the wrapper DIV. Default: akeeba-renderjoomla
 * linkbar_style           Style for linkbars: joomla3|classic. Default: joomla3
 * remove_wrapper_classes  Comma-separated list of classes to REMOVE from the container
 * add_wrapper_classes     Comma-separated list of classes to ADD to the container
 *
 * @package FOF30\Render
 */
class Joomla4 extends Joomla
{
	public function __construct(Container $container)
	{
		$this->priority = 40;
		$this->enabled  = version_compare(JVERSION, '3.9.999', 'gt');

		parent::__construct($container);
	}

	/**
	 * Opens the FEF styling wrapper element. Our component's output will be inside this wrapper.
	 *
	 * @param   array  $classes  An array of additional CSS classes to add to the outer page wrapper element.
	 *
	 * @return  void
	 */
	protected function openPageWrapper(array $classes): void
	{
		$classes[] = 'akeeba-renderer-joomla4';

		parent::openPageWrapper($classes);
	}
}
