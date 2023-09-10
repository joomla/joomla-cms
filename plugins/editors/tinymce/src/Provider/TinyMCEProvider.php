<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\Provider;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Editor\AbstractEditorProvider;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Editors\TinyMCE\PluginTraits\DisplayTrait;
use Joomla\Registry\Registry;

/**
 * Editor provider class
 *
 * @since   __DEPLOY_VERSION__
 */
final class TinyMCEProvider extends AbstractEditorProvider
{
    use DisplayTrait;
    use DatabaseAwareTrait;

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
    public function __construct(
        Registry $params,
        CMSApplicationInterface $application,
        DispatcherInterface $dispatcher,
        DatabaseInterface $database
    ) {
        $this->params      = $params;
        $this->application = $application;

        $this->setDispatcher($dispatcher);
        $this->setDatabase($database);
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
}
