<?php
/**
 * Implements the CronOption class, used by com_cronjobs to refer to plugin defined jobs.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 *
 * @TODO : Review! Is it too convoluted? Plugins could perhaps call the CronOption constructor themselves. Or a change of
 *         documentation might suffice.
 */

namespace Joomla\Component\Cronjobs\Administrator\Cronjobs;

use Joomla\CMS\Language\Text;

/**
 * The CronOption class.
 * Each plugin supporting jobs calls the CronOptions addOptions() method with an array of CronOption constructor argument pairs as argument.
 * Internally, the CronOption object generates the job title and description from the language constant prefix.
 *
 * @since __DEPLOY_VERSION__
 */
class CronOption
{
	/**
	 * Job title
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	public $title;

	/**
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	public $desc;

	/**
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	public $id;

	/**
	 * CronOption constructor.
	 *
	 * @param   string  $id               A unique string for the job routine used internally by the job plugin.
	 * @param   string  $langConstPrefix  The Language constant prefix $p. Expects $p . _TITLE and $p . _DESC to exist.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(string $id, string $langConstPrefix)
	{
		$this->id = $id;
		$this->title = Text::_("${langConstPrefix}_TITLE");
		$this->desc = Text::_("${langConstPrefix}_DESC");
	}
}
