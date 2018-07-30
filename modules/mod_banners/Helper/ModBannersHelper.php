<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Module\Banners\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\Component\Banners\Site\Model\BannersModel;
use Joomla\Registry\Registry;

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
	 * @param   Registry        $params  The module parameters
	 * @param   BannersModel    $model   The model
	 * @param   CMSApplication  $app     The application
	 *
	 * @return  mixed
	 */
	public static function getList(Registry $params, BannersModel $model, CMSApplication $app)
	{
		$keywords = explode(',', $app->getDocument()->getMetaData('keywords'));

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
