<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesArchive\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Helper for mod_articles_archive
 *
 * @since  1.5
 */
class ArticlesArchiveHelper implements DatabaseAwareInterface
{
	use DatabaseAwareTrait;
	/**

	 * Retrieve list of archived articles.
	 *
	 * @param   Registry         $params  Object holding the module parameters
	 * @param   SiteApplication  $app     The app
	 *
	 * @return  array
	 *
	 * @since 4.2.0
	 */
	public function getArticles(Registry $params, SiteApplication $app)
	{
		$db        = $this->getDatabase();
		$query     = $db->getQuery(true);

		$query->select($query->month($db->quoteName('created')) . ' AS created_month')
			->select('MIN(' . $db->quoteName('created') . ') AS created')
			->select($query->year($db->quoteName('created')) . ' AS created_year')
			->from($db->quoteName('#__content', 'c'))
			->where($db->quoteName('c.state') . ' = ' . ContentComponent::CONDITION_ARCHIVED)
			->group($query->year($db->quoteName('c.created')) . ', ' . $query->month($db->quoteName('c.created')))
			->order($query->year($db->quoteName('c.created')) . ' DESC, ' . $query->month($db->quoteName('c.created')) . ' DESC');

		// Filter by language
		if ($app->getLanguageFilter())
		{
			$query->whereIn($db->quoteName('language'), [$app->getLanguage()->getTag(), '*'], ParameterType::STRING);
		}

		$query->setLimit((int) $params->def('count', 10));
		$db->setQuery($query);

		try
		{
			$rows = (array) $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return [];
		}

		$menu   = $app->getMenu();
		$item   = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
		$itemid = (isset($item) && !empty($item->id)) ? '&Itemid=' . $item->id : '';

		$i     = 0;
		$lists = array();

		foreach ($rows as $row)
		{
			$date = Factory::getDate($row->created);

			$createdMonth = $date->format('n');
			$createdYear  = $date->format('Y');

			$createdYearCal = HTMLHelper::_('date', $row->created, 'Y');
			$monthNameCal   = HTMLHelper::_('date', $row->created, 'F');

			$lists[$i] = new \stdClass;

			$lists[$i]->link = Route::_('index.php?option=com_content&view=archive&year=' . $createdYear . '&month=' . $createdMonth . $itemid);
			$lists[$i]->text = Text::sprintf('MOD_ARTICLES_ARCHIVE_DATE', $monthNameCal, $createdYearCal);

			$i++;
		}

		return $lists;
	}

	/**
	 * Retrieve list of archived articles
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array
	 *
	 * @since   1.5
	 *
	 * @deprecated 5.0 Use the none static function getArticles
	 */
	public static function getList(&$params)
	{
		return (new self)->getArticles($params, Factory::getApplication());
	}
}
