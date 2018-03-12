<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.redirect
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Plugin class for redirect handling.
 *
 * @since  1.6
 */
class PlgSystemRedirect extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	protected $autoloadLanguage = false;

	/**
	 * The global exception handler registered before the plugin was instantiated
	 *
	 * @var    callable
	 * @since  3.6
	 */
	private static $previousExceptionHandler;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Set the JError handler for E_ERROR to be the class' handleError method.
		JError::setErrorHandling(E_ERROR, 'callback', array('PlgSystemRedirect', 'handleError'));

		// Register the previously defined exception handler so we can forward errors to it
		self::$previousExceptionHandler = set_exception_handler(array('PlgSystemRedirect', 'handleException'));
	}

	/**
	 * Method to handle an error condition from JError.
	 *
	 * @param   JException  $error  The JException object to be handled.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function handleError(JException $error)
	{
		self::doErrorHandling($error);
	}

	/**
	 * Method to handle an uncaught exception.
	 *
	 * @param   Exception|Throwable  $exception  The Exception or Throwable object to be handled.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 * @throws  InvalidArgumentException
	 */
	public static function handleException($exception)
	{
		// If this isn't a Throwable then bail out
		if (!($exception instanceof Throwable) && !($exception instanceof Exception))
		{
			throw new InvalidArgumentException(
				sprintf('The error handler requires an Exception or Throwable object, a "%s" object was given instead.', get_class($exception))
			);
		}

		self::doErrorHandling($exception);
	}

	/**
	 * Internal processor for all error handlers
	 *
	 * @param   Exception|Throwable  $error  The Exception or Throwable object to be handled.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private static function doErrorHandling($error)
	{
		$app = JFactory::getApplication();

		if ($app->isClient('administrator') || ((int) $error->getCode() !== 404))
		{
			// Proxy to the previous exception handler if available, otherwise just render the error page
			if (self::$previousExceptionHandler)
			{
				call_user_func_array(self::$previousExceptionHandler, array($error));
			}
			else
			{
				JErrorPage::render($error);
			}
		}

		$uri = JUri::getInstance();

		// These are the original URLs
		$orgurl                = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment')));
		$orgurlRel             = rawurldecode($uri->toString(array('path', 'query', 'fragment')));

		// The above doesn't work for sub directories, so do this
		$orgurlRootRel         = str_replace(JUri::root(), '', $orgurl);

		// For when users have added / to the url
		$orgurlRootRelSlash    = str_replace(JUri::root(), '/', $orgurl);
		$orgurlWithoutQuery    = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'fragment')));
		$orgurlRelWithoutQuery = rawurldecode($uri->toString(array('path', 'fragment')));

		// These are the URLs we save and use
		$url                = StringHelper::strtolower(rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment'))));
		$urlRel             = StringHelper::strtolower(rawurldecode($uri->toString(array('path', 'query', 'fragment'))));

		// The above doesn't work for sub directories, so do this
		$urlRootRel         = str_replace(JUri::root(), '', $url);

		// For when users have added / to the url
		$urlRootRelSlash    = str_replace(JUri::root(), '/', $url);
		$urlWithoutQuery    = StringHelper::strtolower(rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'fragment'))));
		$urlRelWithoutQuery = StringHelper::strtolower(rawurldecode($uri->toString(array('path', 'fragment'))));

		$plugin = JPluginHelper::getPlugin('system', 'redirect');

		$params = new Registry($plugin->params);

		$excludes = (array) $params->get('exclude_urls');

		$skipUrl = false;

		foreach ($excludes as $exclude)
		{
			if (empty($exclude->term))
			{
				continue;
			}

			if (!empty($exclude->regexp))
			{
				// Only check $url, because it includes all other sub urls
				if (preg_match('/' . $exclude->term . '/i', $orgurlRel))
				{
					$skipUrl = true;
					break;
				}
			}
			else
			{
				if (StringHelper::strpos($orgurlRel, $exclude->term))
				{
					$skipUrl = true;
					break;
				}
			}
		}

		// Why is this (still) here?
		if ($skipUrl || (strpos($url, 'mosConfig_') !== false) || (strpos($url, '=http://') !== false))
		{
			JErrorPage::render($error);
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('*')
			->from($db->quoteName('#__redirect_links'))
			->where(
				'('
				. $db->quoteName('old_url') . ' = ' . $db->quote($url)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRel)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRootRel)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRootRelSlash)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlWithoutQuery)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRelWithoutQuery)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurl)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRel)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRootRel)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRootRelSlash)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlWithoutQuery)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($orgurlRelWithoutQuery)
				. ')'
			);

		$db->setQuery($query);

		$redirect = null;

		try
		{
			$redirects = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
		}

		$possibleMatches = array_unique(
			array(
				$url,
				$urlRel,
				$urlRootRel,
				$urlRootRelSlash,
				$urlWithoutQuery,
				$urlRelWithoutQuery,
				$orgurl,
				$orgurlRel,
				$orgurlRootRel,
				$orgurlRootRelSlash,
				$orgurlWithoutQuery,
				$orgurlRelWithoutQuery,
			)
		);

		foreach ($possibleMatches as $match)
		{
			if (($index = array_search($match, array_column($redirects, 'old_url'))) !== false)
			{
				$redirect = (object) $redirects[$index];

				if ((int) $redirect->published === 1)
				{
					break;
				}
			}
		}

		// A redirect object was found and, if published, will be used
		if ($redirect !== null && ((int) $redirect->published === 1))
		{
			if (!$redirect->header || (bool) JComponentHelper::getParams('com_redirect')->get('mode', false) === false)
			{
				$redirect->header = 301;
			}

			if ($redirect->header < 400 && $redirect->header >= 300)
			{
				$urlQuery = $uri->getQuery();

				$oldUrlParts = parse_url($redirect->old_url);

				if ($urlQuery !== '' && empty($oldUrlParts['query']))
				{
					$redirect->new_url .= '?' . $urlQuery;
				}

				$dest = JUri::isInternal($redirect->new_url) || strpos($redirect->new_url, 'http') === false ?
					JRoute::_($redirect->new_url) : $redirect->new_url;

				// In case the url contains double // lets remove it
				$destination = str_replace(JUri::root() . '/', JUri::root(), $dest);

				$app->redirect($destination, (int) $redirect->header);
			}

			JErrorPage::render(new RuntimeException($error->getMessage(), $redirect->header, $error));
		}
		// No redirect object was found so we create an entry in the redirect table
		elseif ($redirect === null)
		{
			$params = new Registry(JPluginHelper::getPlugin('system', 'redirect')->params);

			if ((bool) $params->get('collect_urls', true))
			{
				$data = (object) array(
					'id' => 0,
					'old_url' => $url,
					'referer' => $app->input->server->getString('HTTP_REFERER', ''),
					'hits' => 1,
					'published' => 0,
					'created_date' => JFactory::getDate()->toSql()
				);

				try
				{
					$db->insertObject('#__redirect_links', $data, 'id');
				}
				catch (Exception $e)
				{
					JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
				}
			}
		}
		// We have an unpublished redirect object, increment the hit counter
		else
		{
			$redirect->hits++;

			try
			{
				$db->updateObject('#__redirect_links', $redirect, 'id');
			}
			catch (Exception $e)
			{
				JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
			}
		}

		// Proxy to the previous exception handler if available, otherwise just render the error page
		if (self::$previousExceptionHandler)
		{
			call_user_func_array(self::$previousExceptionHandler, array($error));
		}
		else
		{
			JErrorPage::render($error);
		}
	}
}
