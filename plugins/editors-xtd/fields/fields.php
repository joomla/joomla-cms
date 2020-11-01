<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

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
	 * @return  CMSObject  The button options as JObject
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

		// Validate context.
		$context = implode('.', FieldsHelper::extract($context));
		if (!FieldsHelper::getFields($context))
		{
			return;
		}

		$link = 'index.php?option=com_fields&amp;view=fields&amp;layout=modal&amp;tmpl=component&amp;context='
			. $context . '&amp;editor=' . $name . '&amp;' . Session::getFormToken() . '=1';

		$button          = new CMSObject;
		$button->modal   = true;
		$button->link    = $link;
		$button->text    = Text::_('PLG_EDITORS-XTD_FIELDS_BUTTON_FIELD');
		$button->name    = 'puzzle';
		$button->iconSVG = '<svg viewBox="0 0 32 32" width="24" height="24"><path d="M29.948 8.008h-5.264c-1.357-0.002-2.709-0.094-1.391-2.421 1'
							. '.321-2.331 2.254-5.595-3.050-5.595s-4.371 3.264-3.050 5.595c1.319 2.327-0.034 2.418-1.391 2.421h-5.746c-1.129 0-2'
							. '.052 0.924-2.052 2.052v6.387c0 1.36 0.369 2.72-1.962 1.399s-6.042-2.254-6.042 3.050c0 5.303 3.711 4.371 6.042 3.0'
							. '50s1.962 0.039 1.962 1.399v4.611c0 1.129 0.924 2.052 2.052 2.052h5.738c1.36 0 2.72-0.544 1.399-2.875s-2.254-5.595'
							. ' 3.050-5.595 4.371 3.264 3.050 5.595c-1.321 2.331 0.039 2.875 1.399 2.875h5.256c1.129 0 2.052-0.924 2.052-2.052v-'
							. '19.896c0-1.129-0.923-2.052-2.052-2.052z"></path></svg>';
		$button->options = [
			'height'     => '300px',
			'width'      => '800px',
			'bodyHeight' => '70',
			'modalWidth' => '80',
		];

		return $button;
	}
}
