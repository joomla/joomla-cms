<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('_JEXEC') or die;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Psr\Link\EvolvableLinkProviderInterface;

/**
 * Joomla! Preload Manager
 *
 * @since  __DEPLOY_VERSION__
 */
class PreloadManager implements PreloadManagerInterface
{
	/**
	 * The link provider
	 *
	 * @var    EvolvableLinkProviderInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $linkProvider;

	/**
	 * PreloadManager constructor
	 *
	 * @param   EvolvableLinkProviderInterface  $linkProvider  The link provider
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(EvolvableLinkProviderInterface $linkProvider = null)
	{
		$this->linkProvider = $linkProvider ?: new GenericLinkProvider;
	}

	/**
	 * Get the link provider
	 *
	 * @return  EvolvableLinkProviderInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLinkProvider()
	{
		return $this->linkProvider;
	}

	/**
	 * Set the link provider
	 *
	 * @param   EvolvableLinkProviderInterface  $linkProvider  The link provider
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setLinkProvider(EvolvableLinkProviderInterface $linkProvider)
	{
		$this->linkProvider = $linkProvider;

		return $this;
	}

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
	public function preload($uri, array $attributes = [])
	{
		$this->link($uri, 'preload', $attributes);
	}

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
	public function dnsPrefetch($uri, array $attributes = [])
	{
		$this->link($uri, 'dns-prefetch', $attributes);
	}

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
	public function preconnect($uri, array $attributes = [])
	{
		$this->link($uri, 'preconnect', $attributes);
	}

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
	public function prefetch($uri, array $attributes = [])
	{
		$this->link($uri, 'prefetch', $attributes);
	}

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
	public function prerender($uri, array $attributes = [])
	{
		$this->link($uri, 'prerender', $attributes);
	}

	/**
	 * Adds a "Link" HTTP header.
	 *
	 * @param   string  $uri         The relation URI
	 * @param   string  $rel         The relation type (e.g. "preload", "prefetch", "prerender" or "dns-prefetch")
	 * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function link($uri, $rel, array $attributes = [])
	{
		$link = new Link($rel, $uri);

		foreach ($attributes as $key => $value)
		{
			$link = $link->withAttribute($key, $value);
		}

		$this->setLinkProvider($this->getLinkProvider()->withLink($link));
	}
}
