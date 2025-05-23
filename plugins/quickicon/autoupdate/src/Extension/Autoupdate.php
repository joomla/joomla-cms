<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Autoupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\Autoupdate\Extension;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! auto update health check notification plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class Autoupdate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * The document.
     *
     * @var Document
     *
     * @since  __DEPLOY_VERSION__
     */
    private $document;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'getAutoUpdateStatusNotification',
        ];
    }

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $dispatcher   The object to observe
     * @param   Document             $document     The document
     * @param   array                $config       An optional associative array of configuration settings.
     *                                             Recognized key values include 'name', 'group', 'params', 'language'
     *                                             (this list is not meant to be comprehensive).
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($dispatcher, Document $document, array $config = [])
    {
        parent::__construct($dispatcher, $config);

        $this->document = $document;
    }

    /**
     * This method is called when the Quick Icons module is constructing its set
     * of icons. You can return an array which defines a single icon and it will
     * be rendered right after the stock Quick Icons.
     *
     * @param   QuickIconsEvent  $event  The event object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getAutoUpdateStatusNotification(QuickIconsEvent $event)
    {
        $context = $event->getContext();

        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->getApplication()->getIdentity()->authorise('core.admin', 'com_joomlaupdate')
        ) {
            return;
        }

        Text::script('PLG_QUICKICON_AUTOUPDATE_ERROR');
        Text::script('PLG_QUICKICON_AUTOUPDATE_OK');
        Text::script('PLG_QUICKICON_AUTOUPDATE_OUTDATED');
        Text::script('PLG_QUICKICON_AUTOUPDATE_DISABLED');
        Text::script('MESSAGE');
        Text::script('ERROR');
        Text::script('INFO');
        Text::script('WARNING');

        $this->document->addScriptOptions(
            'js-auto-update',
            [
                'url'     => Uri::base() . 'index.php?option=com_joomlaupdate',
                'ajaxUrl' => Uri::base() . 'index.php?option=com_joomlaupdate&task=update.healthstatus&'
                    . Session::getFormToken() . '=1',
            ]
        );

        $this->document->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_autoupdate', 'plg_quickicon_autoupdate/healthcheck.min.js', [], ['defer' => true], ['core']);

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = [
            [
                'link'  => 'index.php?option=com_config&view=component&component=com_joomlaupdate#automated-updates',
                'image' => 'icon-health',
                'icon'  => '',
                'text'  => Text::_('PLG_QUICKICON_AUTOUPDATE_CHECKING'),
                'id'    => 'plg_quickicon_autoupdate',
                'group' => 'MOD_QUICKICON_MAINTENANCE',
            ],
        ];

        $event->setArgument('result', $result);
    }
}
