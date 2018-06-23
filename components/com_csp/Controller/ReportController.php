<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Csp\Administrator\Table\ReportTable;
use Joomla\Registry\Registry;

/**
 * Csp Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class ReportController extends BaseController
{
	/**
	 * Log the CSP request
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function log()
	{
		$pluginParams = new Registry;

		// Get the httpheaders plugin params
		if (PluginHelper::isEnabled('system', 'httpheaders'))
		{
			$pluginParams->loadString(PluginHelper::getPlugin('system', 'httpheaders')->params);
		}

		// When we are not in detect mode do nothing here
		if ($pluginParams->get('contentsecuritypolicy_mode', 'custom') != 'detect')
		{
			$this->app->close();
		}

		$data = $this->input->json->get('csp-report', array(), 'Array');

		if (empty($data))
		{
			$this->app->close();
		}

		$report = new \stdClass;
		$report->document_uri = $data['document-uri'];
		$report->blocked_uri  = $data['blocked-uri'];

		if (filter_var($report->blocked_uri, FILTER_VALIDATE_URL) !== false)
		{
			$parsedUrl = parse_url($report->blocked_uri);
			$report->blocked_uri = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
		}

		// Eval or inline lets make sure they get reported in the correct way
		if (in_array($report->blocked_uri, ['eval', 'inline']))
		{
			$report->blocked_uri = "'unsafe-" . $report->blocked_uri;
		}

		$report->directive = $data['violated-directive'];

		if (empty($data['violated-directive']) && !empty($data['effective-directive']))
		{
			$report->directive = $data['effective-directive'];
		}

		// Empty report
		if (empty($report->blocked_uri) && empty($report->directive))
		{
			$this->app->close();
		}

		$now = Factory::getDate()->toSql();

		$report->created  = $now;
		$report->modified = $now;

		if ($this->isEntryExisting($report))
		{
			$this->app->close();
		}

		$table = new ReportTable(Factory::getDbo());

		$table->bind($report);
		$table->store();

		$this->app->close();
	}

	/**
	 * Check if we already logged this entry
	 *
	 * @param   object  $report  The generated report row
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isEntryExisting($report)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query
			->select('count(*)')
			->from('#__csp')
			->where($db->quoteName('blocked_uri') . '=' . $db->quote($report->blocked_uri))
			->where($db->quoteName('directive') . '=' . $db->quote($report->directive));

		$db->setQuery($query);

		return $db->loadResult() > 0;
	}
}
