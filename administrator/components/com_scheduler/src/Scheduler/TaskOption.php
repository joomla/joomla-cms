<?php
/**
 * Implements the TaskOption class, used by com_scheduler to refer to plugin task routines.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Tasks;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * The TaskOption class.
 * Each plugin supporting tasks calls the TaskOptions addOptions() method with an array of TaskOption constructor argument pairs as argument.
 * Internally, the TaskOption object generates the routine title and description from the language constant prefix.
 *
 * @since  __DEPLOY_VERSION__
 */
class TaskOption
{
	/**
	 * Task routine title
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $title;

	/**
	 * Task routine description
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $desc;

	/**
	 * Routine type-ID
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type;

	/**
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $langConstPrefix;

	/**
	 * TaskOption constructor.
	 *
	 * @param   string  $type             A unique ID string for a plugin task routine.
	 * @param   string  $langConstPrefix  The Language constant prefix $p. Expects $p . _TITLE and $p . _DESC to exist.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(string $type, string $langConstPrefix)
	{
		$this->type = $type;
		$this->title = Text::_("${langConstPrefix}_TITLE");
		$this->desc = Text::_("${langConstPrefix}_DESC");
		$this->langConstPrefix = $langConstPrefix;
	}

	/**
	 * Overload the __get() magic method to give access to private properties
	 *
	 * @param   string  $name  The object property requested.
	 *
	 * @return  mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __get(string $name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}

		return null;
	}
}
