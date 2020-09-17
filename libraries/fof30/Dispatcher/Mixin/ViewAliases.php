<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Dispatcher\Mixin;

defined('_JEXEC') || die;

use Joomla\CMS\Uri\Uri;

/**
 * Lets you create view aliases. When you access a view alias the real view is loaded instead. You can optionally have
 * an HTTPS 301 redirection for GET requests to URLs that use the view name alias.
 *
 * IMPORTANT: This is a mixin (or, as we call it in PHP, a trait). Traits require PHP 5.4 or later. If you opt to use
 * this trait your component will no longer work under PHP 5.3.
 *
 * Usage:
 *
 * • Override $viewNameAliases with your view names map.
 * • If you want to issue HTTP 301 for GET requests set $permanentAliasRedirectionOnGET to true.
 * • If you have an onBeforeDispatch method remember to alias and call this traits' onBeforeDispatch method at the top.
 *
 * Regarding the last point, if you've never used traits before, the code looks like this. Top of the class:
 *   use ViewAliases {
 *       onBeforeDispatch as onBeforeDispatchViewAliases;
 *   }
 * and inside your custom onBeforeDispatch method, the first statement should be:
 *   $this->onBeforeDispatchViewAliases();
 * Simple!
 */
trait ViewAliases
{
	/**
	 * Maps view name aliases to actual views. The format is 'alias' => 'RealView'.
	 *
	 * @var  array
	 */
	protected $viewNameAliases = [];

	/**
	 * If set to true, any GET request to the alias view will result in an HTTP 301 permanent redirection to the real
	 * view name.
	 *
	 * This does NOT apply to POST, PUT, DELETE etc URLs. When you submit form data you cannot have a redirection. The
	 * browser will _typically_ not resend the submitted data.
	 *
	 * @var  bool
	 */
	protected $permanentAliasRedirectionOnGET = false;

	/**
	 * Transparently replaces old view names with their counterparts.
	 *
	 * If you are overriding this method in your component remember to alias it and call it from your overridden method.
	 */
	protected function onBeforeDispatch()
	{
		if (!array_key_exists($this->view, $this->viewNameAliases))
		{
			return;
		}

		$this->view = $this->viewNameAliases[$this->view];
		$this->container->input->set('view', $this->view);

		// Perform HTTP 301 Moved permanently redirection on GET requests if requested to do so
		if ($this->permanentAliasRedirectionOnGET && isset($_SERVER['REQUEST_METHOD'])
			&& (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET')
		)
		{
			$url = Uri::getInstance();
			$url->setVar('view', $this->view);

			$this->container->platform->redirect($url, 301);
		}
	}
}
