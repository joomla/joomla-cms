<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Module\Banners\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Component\Banners\Site\Model\BannersModel;

/**
 * Helper for mod_banners
 *
 * @since  1.5
 */
class ModBannersHelper
{
	/**
	 * Retrieve list of banners
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  mixed
	 */
	public static function &getList(&$params)
	{
		$document = Factory::getDocument();
		$app      = Factory::getApplication();
		$keywords = explode(',', $document->getMetaData('keywords'));

		$model = new BannersModel(array('ignore_request' => true));
		$model->setState('filter.client_id', (int) $params->get('cid'));
		$model->setState('filter.category_id', $params->get('catid', array()));
		$model->setState('list.limit', (int) $params->get('count', 1));
		$model->setState('list.start', 0);
		$model->setState('filter.ordering', $params->get('ordering'));
		$model->setState('filter.tag_search', $params->get('tag_search'));
		$model->setState('filter.keywords', $keywords);
		$model->setState('filter.language', $app->getLanguageFilter());

		$banners = $model->getItems();

		if ($banners)
		{
			$model->impress();
		}

		return $banners;
	}
}
