<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\CMS\Component\ComponentHelper;

/**
 * System plugin to manage cookies.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemCookiemanager extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;


	/**
	 * After Render Event.
	 *
	 * @return   void
	 *
	 * @since		 __DEPLOY_VERSION__
	 */
	public function onBeforeRender()
	{

		if (!$this->app->isClient('site'))
		{
			return;
		}

			$lang = Factory::getLanguage();
			$lang->load('com_cookiemanager', JPATH_ADMINISTRATOR);

			$params = ComponentHelper::getParams('com_cookiemanager');
			$p=$params->get('policylink');
			$s = $this->app->getMenu();
			$m=$s->getItem($p);
// $m = $sitemenu->getItems($p);
		$modal=HTMLHelper::_(
				'bootstrap.renderModal',
				'exampleModal',
				[
					'title' => 	Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_TITLE'),
					'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_REVOKE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#preferences">'
					. Text::_('COM_COOKIEMANAGER_PREFERENCE_BUTTON_TEXT') . '</button>'
					. '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
					. Text::_('COM_COOKIEMANAGER_ACCEPT_BUTTON_TEXT') . '</button>',

				],
				$body= Text::_('COM_COOKIEMANAGER_COOKIE_BANNER_DESCRIPTION').'<br><br><a '.
							' href="'.$m->link.'">'.Text::_('COM_COOKIEMANAGER_VIEW_COOKIE_POLICY').'</a>'
			);
		//
			echo $modal;

			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(['title','description']))
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('extension').' = '. $db->quote('com_cookiemanager'));

			$db->setQuery($query);
			$cat = 	 $db->loadObjectList();
print_r($cat[0]->{'description'});

			$modal2=HTMLHelper::_(
					'bootstrap.renderModal',
					'preferences',
					[
						'title' => 	Text::_('COM_COOKIEMANAGER_PREFERENCE_BUTTON_TEXT'),
						'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
						. Text::_('COM_COOKIEMANAGER_REVOKE_BUTTON_TEXT') . '</button>',

					],

				);
				echo $modal2;
			}

}



?>
<script>
window.addEventListener('load', (event) => {
	var myModal = new bootstrap.Modal(document.getElementById('exampleModal'))
myModal.show()

});
</script>
