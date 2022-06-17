<?php

/**
 * File Doc Comment_
 * PHP version 5
 *
 * @category  Component
 * @package   Joomla.Administrator
 * @author    Joomla! <admin@joomla.org>
 * @copyright (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @link      admin@joomla.org
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * PlgSystemTour
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemTour extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $guide;
	/**
	 * function for getSubscribedEvents : new Joomla 4 feature
	 *
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeRender' => 'onBeforeRender',
			'onBeforeCompileHead' => 'onBeforeCompileHead'
		];
	}
	/**
	 * Listener for the `onBeforeRender` event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeRender()
	{
		// Run in backend
		if ($this->app->isClient('administrator'))
		{
			/**
			 * Booting of the Component to get the data in JSON Format
			 */
			$myTours = $this->app->bootComponent('com_guidedtours')->getMVCFactory()->createModel('Tours', 'Administrator', ['ignore_request' => true]);
			$mySteps = $this->app->bootComponent('com_guidedtours')->getMVCFactory()->createModel('Steps', 'Administrator', ['ignore_request' => true]);

			$theCurrentExtension = $this->app->input->get('option');
			$myTours->setState('list.extensions', $theCurrentExtension);

			$myTours->setState('filter.published', 1);

			$steps = $mySteps->getItems();
			$tours = $myTours->getItems();

			$document = Factory::getDocument();

			$newsteps = [];

			foreach ($steps as $step)
			{
				if (!isset($newsteps[$step->tour_id]))
				{
					$newsteps[$step->tour_id] = [];
				}

				$newsteps[$step->tour_id][] = $step;
			}

			foreach ($tours as $tour)
			{
				$tour->steps = [];

				if (isset($newsteps[$tour->id]))
				{
					$tour->steps = $newsteps[$tour->id];
				}
			}

			$myTours = json_encode($tours);
			$mySteps = json_encode($steps);
			$document->addScriptOptions('myTours', $myTours);
			$document->addScriptOptions('mySteps', $mySteps);

			$toolbar = Toolbar::getInstance('toolbar');
			$dropdown = $toolbar->dropdownButton()
				->text('Take the Tour')
				->toggleSplit(false)
				->icon('fa fa-map-signs')
				->buttonClass('btn btn-action');

			$childBar = $dropdown->getChildToolbar();

			foreach ($tours as $a)
			{
				$childBar->BasicButton('tour')
					->text($a->title)
					->attributes(['data-id' => $a->id])
					->buttonClass('btn btn-primary');
			}
		}
	}

	/**
	 * Listener for the `onBeforeCompileHead` event
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{

		if ($this->app->isClient('administrator'))
		{
			$this->app->getDocument()->getWebAssetManager()
				->usePreset('shepherdjs');

			// Load required assets
			$assets = $this->app->getDocument()->getWebAssetManager();
			$assets->registerAndUseScript(
				'plg_system_tour.script',
				'plg_system_tour/guide.min.js',
				[],
				['defer' => true],
				['core']
			);
			$assets->registerAndUseStyle(
				'plg_system_tour.style',
				'plg_system_tour/guide.min.css'
			);
		}
	}
}
