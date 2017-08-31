<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('_JEXEC') or die;

use Psr\Link\EvolvableLinkProviderInterface;

/**
 * Joomla! Preload Manager Interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface PreloadManagerInterface
{
	/**
	 * Get the link provider
	 *
	 * @return  EvolvableLinkProviderInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLinkProvider();

	/**
	 * Set the link provider
	 *
	 * @param   EvolvableLinkProviderInterface  $linkProvider  The link provider
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setLinkProvider(EvolvableLinkProviderInterface $linkProvider);

	/**
	 * Preloads a resource.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('crossorigin' => 'use-credentials')")
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function preload($uri, array $attributes = []);

	/**
	 * Resolves a resource origin as early as possible.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dnsPrefetch($uri, array $attributes = []);

	/**
	 * Initiates a early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function preconnect($uri, array $attributes = []);

	/**
	 * Indicates to the client that it should prefetch this resource.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function prefetch($uri, array $attributes = []);

	/**
	 * Indicates to the client that it should prerender this resource.
	 *
	 * @param   string  $uri         A public path
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function prerender($uri, array $attributes = []);
}
