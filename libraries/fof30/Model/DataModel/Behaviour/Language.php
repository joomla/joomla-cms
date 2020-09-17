<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model\DataModel\Behaviour;

defined('_JEXEC') || die;

use FOF30\Event\Observer;
use FOF30\Model\DataModel;
use JDatabaseQuery;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use PlgSystemLanguageFilter;

/**
 * FOF model behavior class to filter front-end access to items
 * based on the language.
 *
 * @since    2.1
 */
class Language extends Observer
{
	/** @var  PlgSystemLanguageFilter */
	protected $lang_filter_plugin;

	/**
	 * This event runs before we have built the query used to fetch a record
	 * list in a model. It is used to blacklist the language filter
	 *
	 * @param   DataModel       &$model  The model which calls this event
	 * @param   JDatabaseQuery  &$query  The model which calls this event
	 *
	 * @return  void
	 */
	public function onBeforeBuildQuery(&$model, &$query)
	{
		if ($model->getContainer()->platform->isFrontend())
		{
			$model->blacklistFilters('language');
		}

		// Make sure the field actually exists AND we're not in CLI
		if (!$model->hasField('language') || $model->getContainer()->platform->isCli())
		{
			return;
		}

		/** @var SiteApplication $app */
		$app               = Factory::getApplication();
		$hasLanguageFilter = method_exists($app, 'getLanguageFilter');

		if ($hasLanguageFilter)
		{
			$hasLanguageFilter = $app->getLanguageFilter();
		}

		if (!$hasLanguageFilter)
		{
			return;
		}

		// Ask Joomla for the plugin only if we don't already have it. Useful for tests
		if (!$this->lang_filter_plugin)
		{
			$this->lang_filter_plugin = PluginHelper::getPlugin('system', 'languagefilter');
		}

		$lang_filter_params = class_exists('JRegistry') ? new Registry($this->lang_filter_plugin->params) : new Registry($this->lang_filter_plugin->params);

		$languages = ['*'];

		if ($lang_filter_params->get('remove_default_prefix'))
		{
			// Get default site language
			$platform    = $model->getContainer()->platform;
			$lg          = $platform->getLanguage();
			$languages[] = $lg->getTag();
		}
		else
		{
			// We have to use JInput since the language fragment is not set in the $_REQUEST, thus we won't have it in our model
			// TODO Double check the previous assumption
			$languages[] = Factory::getApplication()->input->getCmd('language', '*');
		}

		// Filter out double languages
		$languages = array_unique($languages);

		// And filter the query output by these languages
		$db        = $model->getDbo();
		$languages = array_map([$db, 'quote'], $languages);
		$fieldName = $model->getFieldAlias('language');

		$model->whereRaw($db->qn($fieldName) . ' IN(' . implode(', ', $languages) . ')');
	}

	/**
	 * The event runs after DataModel has retrieved a single item from the database. It is used to apply automatic
	 * filters.
	 *
	 * @param   DataModel &$model  The model which was called
	 * @param   Array     &$keys   The keys used to locate the record which was loaded
	 *
	 * @return  void
	 */
	public function onAfterLoad(&$model, &$keys)
	{
		// Make sure we have a DataModel
		if (!($model instanceof DataModel))
		{
			return;
		}

		// Make sure the field actually exists AND we're not in CLI
		if (!$model->hasField('language') || $model->getContainer()->platform->isCli())
		{
			return;
		}

		// Make sure it is a multilingual site and get a list of languages
		/** @var SiteApplication $app */
		$app               = Factory::getApplication();
		$hasLanguageFilter = method_exists($app, 'getLanguageFilter');

		if ($hasLanguageFilter)
		{
			$hasLanguageFilter = $app->getLanguageFilter();
		}

		if (!$hasLanguageFilter)
		{
			return;
		}

		// Ask Joomla for the plugin only if we don't already have it. Useful for tests
		if (!$this->lang_filter_plugin)
		{
			$this->lang_filter_plugin = PluginHelper::getPlugin('system', 'languagefilter');
		}

		$lang_filter_params = class_exists('JRegistry') ? new Registry($this->lang_filter_plugin->params) : new Registry($this->lang_filter_plugin->params);

		$languages = ['*'];

		if ($lang_filter_params->get('remove_default_prefix'))
		{
			// Get default site language
			$lg          = $model->getContainer()->platform->getLanguage();
			$languages[] = $lg->getTag();
		}
		else
		{
			$languages[] = Factory::getApplication()->input->getCmd('language', '*');
		}

		// Filter out double languages
		$languages = array_unique($languages);

		// Filter by language
		if (!in_array($model->getFieldValue('language'), $languages))
		{
			$model->reset();
		}
	}
}
