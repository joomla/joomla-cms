<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('BannersHelper', JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php');

JFormHelper::loadFieldClass('list');

/**
 * Bannerclient field.
 *
 * @since  1.6
 */
class JFormFieldBannerClient extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'BannerClient';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		return array_merge(parent::getOptions(), BannersHelper::getClientOptions());
	}
}
