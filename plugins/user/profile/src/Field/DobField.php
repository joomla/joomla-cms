<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\Profile\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\CalendarField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

/**
 * Provides input for "Date of Birth" field
 *
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 * @since       3.3.7
 */
class DobField extends CalendarField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.3.7
	 */
	protected $type = 'Dob';

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   3.3.7
	 */
	protected function getLabel()
	{
		$label = parent::getLabel();

		// Get the info text from the XML element, defaulting to empty.
		$text  = $this->element['info'] ? (string) $this->element['info'] : '';
		$text  = $this->translateLabel ? Text::_($text) : $text;

		if ($text)
		{
			$app    = Factory::getApplication();
			$view   = $app->input->getString('view', '');

			// Only display the tip when editing profile
			if ($view === 'registration' || $view === 'profile' || $app->isClient('administrator'))
			{
				$layout = new FileLayout('plugins.user.profile.fields.dob');
				$info   = $layout->render(array('text' => $text));
				$label  = $info . $label;
			}
		}

		return $label;
	}
}
