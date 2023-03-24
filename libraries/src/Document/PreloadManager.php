<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Fig\Link\GenericLinkProvider;
use Fig\Link\Link;
use Psr\Link\EvolvableLinkProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Preload Manager
 *
 * @since  4.0.0
 */
class PreloadManager implements PreloadManagerInterface
{
    /**
     * The link provider
     *
     * @var    EvolvableLinkProviderInterface
     * @since  4.0.0
     */
    protected $linkProvider;

    /**
     * PreloadManager constructor
     *
     * @param   EvolvableLinkProviderInterface  $linkProvider  The link provider
     *
     * @since   4.0.0
     */
    public function __construct(EvolvableLinkProviderInterface $linkProvider = null)
    {
        $this->linkProvider = $linkProvider ?: new GenericLinkProvider();
    }

    /**
     * Get the link provider
     *
     * @return  EvolvableLinkProviderInterface
     *
     * @since   4.0.0
     */
    public function getLinkProvider(): EvolvableLinkProviderInterface
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
     * @since   4.0.0
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
     * @since   4.0.0
     */
    public function preload(string $uri, array $attributes = [])
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
     * @since   4.0.0
     */
    public function dnsPrefetch(string $uri, array $attributes = [])
    {
        $this->link($uri, 'dns-prefetch', $attributes);
    }

    /**
     * Initiates an early connection to a resource (DNS resolution, TCP handshake, TLS negotiation).
     *
     * @param   string  $uri         A public path
     * @param   array   $attributes  The attributes of this link (e.g. "array('as' => true)", "array('pr' => 0.5)")
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function preconnect(string $uri, array $attributes = [])
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
     * @since   4.0.0
     */
    public function prefetch(string $uri, array $attributes = [])
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
     * @since   4.0.0
     */
    public function prerender(string $uri, array $attributes = [])
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
     * @since   4.0.0
     */
    private function link(string $uri, string $rel, array $attributes = [])
    {
        $link = new Link($rel, $uri);

        foreach ($attributes as $key => $value) {
            $link = $link->withAttribute($key, $value);
        }

        $this->setLinkProvider($this->getLinkProvider()->withLink($link));
    }
}
