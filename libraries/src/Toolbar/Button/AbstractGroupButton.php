<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Toolbar\Toolbar;

/**
 * The AbstractGroupButton class.
 *
 * @since  4.0.0
 */
abstract class AbstractGroupButton extends BasicButton
{
	/**
	 * The child Toolbar instance.
	 *
	 * @var  Toolbar
	 *
	 * @since  4.0.0
	 */
	protected $child;

	/**
	 * Add children buttons as dropdown.
	 *
	 * @param   callable  $handler  The callback to configure dropdown items.
	 *
	 * @return  static
	 *
	 * @since  4.0.0
	 */
	public function configure(callable $handler): self
	{
		$child = $this->getChildToolbar();

		$handler($child);

		return $this;
	}

	/**
	 * Get child toolbar.
	 *
	 * @return  Toolbar  Return new child Toolbar instance.
	 *
	 * @since   4.0.0
	 */
	public function getChildToolbar(): Toolbar
	{
		if (!$this->child)
		{
			$this->child = $this->parent->createChild($this->getName() . '-children');
		}

		return $this->child;
	}

	/**
	 * Get the button CSS Id.
	 *
	 * @return  string  Button CSS Id
	 *
	 * @since   4.0.0
	 */
	protected function fetchId()
	{
		return $this->parent->getName() . '-group-' . $this->getName();
	}
}
