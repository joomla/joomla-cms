<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.fields
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;

/**
 * Editor Fields button
 *
 * @since  3.7.0
 */
class PlgButtonFields extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject|void  The button options as CMSObject
	 *
	 * @since  3.7.0
	 */
	public function onDisplay($name)
	{
		// Check if com_fields is enabled
		if (!ComponentHelper::isEnabled('com_fields'))
		{
			return;
		}

		// Guess the field context based on view.
		$jinput = Factory::getApplication()->input;
		$context = $jinput->get('option') . '.' . $jinput->get('view');

		// Special context for com_categories
		if ($context === 'com_categories.category')
		{
			$context = $jinput->get('extension', 'com_content') . '.categories';
		}

		$link = 'index.php?option=com_fields&amp;view=fields&amp;layout=modal&amp;tmpl=component&amp;context='
			. $context . '&amp;editor=' . $name . '&amp;' . Session::getFormToken() . '=1';

		$button = new CMSObject;
		$button->modal   = true;
		$button->link    = $link;
		$button->text    = Text::_('PLG_EDITORS-XTD_FIELDS_BUTTON_FIELD');
		$button->name    = $this->_type . '_' . $this->_name;
		$button->icon    = 'puzzle';
		$button->iconSVG = '<svg viewBox="0 0 576 512" width="24" height="24"><path d="M519.442 288.651c-41.519 0-59.5 31.593-82.058 31.593C377.'
							. '409 320.244 432 144 432 144s-196.288 80-196.288-3.297c0-35.827 36.288-46.25 36.288-85.985C272 19.216 243.885 0 210.'
							. '539 0c-34.654 0-66.366 18.891-66.366 56.346 0 41.364 31.711 59.277 31.711 81.75C175.885 207.719 0 166.758 0 166.758'
							. 'v333.237s178.635 41.047 178.635-28.662c0-22.473-40-40.107-40-81.471 0-37.456 29.25-56.346 63.577-56.346 33.673 0 61'
							. '.788 19.216 61.788 54.717 0 39.735-36.288 50.158-36.288 85.985 0 60.803 129.675 25.73 181.23 25.73 0 0-34.725-120.1'
							. '01 25.827-120.101 35.962 0 46.423 36.152 86.308 36.152C556.712 416 576 387.99 576 354.443c0-34.199-18.962-65.792-56'
							. '.558-65.792z"></path></svg>';
		$button->options = [
			'height'     => '300px',
			'width'      => '800px',
			'bodyHeight' => '70',
			'modalWidth' => '80',
		];

		return $button;
	}
}
