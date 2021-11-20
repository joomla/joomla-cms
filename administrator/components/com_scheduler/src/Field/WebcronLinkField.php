<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Field;

// Restrict direct access
use Joomla\CMS\Form\Field\TextField;

\defined('_JEXEC') or die;

/**
 * Field to override the text field layout to add a copy-text button, used in the com_scheduler
 * configuration form.
 * This field class is only needed because the layout file is in a non-global directory, so this should
 * be made redundant and removed if/once the layout is shifted to `JPATH_SITE/layout/`
 *
 * @since __DEPLOY_VERSION__
 */
class WebcronLinkField extends TextField
{
	/**
	 * We use a custom layout that allows for the link to be copied.
	 *
	 * @var  string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'form.field.webcron_link';

	/**
	 * Override layout paths.
	 *
	 * @inheritDoc
	 * @return string[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getLayoutPaths(): array
	{
		$s = DIRECTORY_SEPARATOR;

		return [
				JPATH_ADMINISTRATOR . "${s}/components${s}com_scheduler${s}layouts${s}",
			] + parent::getLayoutPaths();
	}
}
