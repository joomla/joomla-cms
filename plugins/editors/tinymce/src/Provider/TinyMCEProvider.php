<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\CodeMirror\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Editor\AbstractEditorProvider;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

/**
 * Editor provider class
 *
 * @since   __DEPLOY_VERSION__
 */
final class TinyMCEProvider extends AbstractEditorProvider
{
    /**
     * A Registry object holding the parameters for the plugin
     *
     * @var    Registry
     * @since  __DEPLOY_VERSION__
     */
    protected $params;

    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    protected $application;

    /**
     * Class constructor
     *
     * @param   Registry                 $params
     * @param   CMSApplicationInterface  $application
     * @param   DispatcherInterface      $dispatcher
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(Registry $params, CMSApplicationInterface $application, DispatcherInterface $dispatcher)
    {
        $this->params      = $params;
        $this->application = $application;

        $this->setDispatcher($dispatcher);
    }

    /**
     * Return Editor name, CMD string.
     *
     * @return string
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return 'tinymce';
    }

    /**
     * Gets the editor HTML markup
     *
     * @param   string  $name        Input name.
     * @param   string  $content     The content of the field.
     * @param   array   $attributes  Associative array of editor attributes.
     * @param   array   $params      Associative array of editor parameters.
     *
     * @return  string  The HTML markup of the editor
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display(string $name, string $content = '', array $attributes = [], array $params = []): string
    {
        return '';
    }
}
