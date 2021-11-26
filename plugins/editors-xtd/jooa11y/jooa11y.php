<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.jooa11y
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/**
 * Editor Jooa11y button
 *
 * @since  _DEPLOY_VERSION_
 */
class PlgButtonJooa11y extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  _DEPLOY_VERSION_
	 */
	protected $autoloadLanguage = true;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject  The button options as JObject
	 *
	 * @since   _DEPLOY_VERSION_
	 */
	public function onDisplay($name)
	{
		/*
		 * Generate the button to open a preview window with the jooa11y checker
		 */
			$link = Route::link(
					'site',
					RouteHelper::getArticleRoute($this->item->id . ':' . $this->item->alias, $this->item->catid, $this->item->language),
					true
			);
			$button = new CMSObject;
			$button->modal   = true;
			$button->link    = $link;
			$button->text    = Text::_('PLG_EDITORS-XTD_JOOA11Y_BUTTON');
			$button->name    = $this->_type . '_' . $this->_name;
			$button->icon    = 'universal-access';
			$button->iconSVG = '<svg viewBox="0 0 512 512" width="24" height="24"><path d="M256 400c-114.971 0 -208 -93.0469 -208 -208c0 -114.971 '
				. '93.0469 -208 208 -208c114.971 0 208 93.0469 208 208c0 114.971 -93.0469 208 -208 208zM256 440c136.967 0 248 -111.033 248 -248s-1'
				. '11.033 -248 -248 -248s-248 111.033 -248 248s111.033 248 248 248z M256 384c106.039 0 192 -85.9609 192 -192s-85.9609 -192 -192 -1'
				. '92s-192 85.9609 -192 192s85.9609 192 192 192zM256 340c-19.8818 0 -36 -16.1182 -36 -36s16.1182 -36 36 -36s36 16.1182 36 36s-16.1'
				. '182 36 -36 36zM373.741 241.977 c8.59961 2.03027 13.9258 10.6484 11.8965 19.249c-2.03027 8.60156 -10.6494 13.9258 -19.249 11.895'
				. '5c-96.4912 -22.7832 -124.089 -22.8291 -220.774 0c-8.60254 2.03125 -17.2178 -3.29395 -19.249 -11.8955c-2.03125 -8.60059 3.29492 '
				. '-17.2178 11.8945 -19.249 c28.7129 -6.7793 55.5127 -12.749 82.1416 -15.8066c-0.852539 -101.08 -12.3242 -123.08 -25.0371 -155.621'
				. 'c-3.61719 -9.25879 0.957031 -19.6982 10.2168 -23.3145c9.26465 -3.61914 19.7002 0.961914 23.3154 10.2168c8.72754 22.3408 17.0947'
				. ' 40.6982 22.2617 78.5488 h9.68555c5.1748 -37.9131 13.5566 -56.2412 22.2617 -78.5488c3.61621 -9.25977 14.0547 -13.834 23.3154 -1'
				. '0.2168c9.25977 3.61621 13.834 14.0547 10.2168 23.3145c-12.7305 32.5693 -24.1855 54.5986 -25.0371 155.621c26.6299 3.05859 53.428'
				. '7 9.02832 82.1406 15.8066 z"></path></svg>';
			$button->options = [
				'height'     => '300px',
				'width'      => '800px',
				'bodyHeight' => '70',
				'modalWidth' => '80',
			];

			return $button;
	}
}
