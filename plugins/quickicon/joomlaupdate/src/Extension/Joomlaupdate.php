<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Joomlaupdate
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\Joomlaupdate\Extension;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

/**
 * Joomla! update notification plugin
 *
 * @since  2.5
 */
class Joomlaupdate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * The document.
     *
     * @var Document
     *
     * @since  4.0.0
     */
    private $document;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'getCoreUpdateNotification',
        ];
    }

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $subject   The object to observe
     * @param   Document             $document  The document
     * @param   array                $config    An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'group', 'params', 'language'
     *                                          (this list is not meant to be comprehensive).
     *
     * @since   4.0.0
     */
    public function __construct($subject, Document $document, $config = array())
    {
        parent::__construct($subject, $config);

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
     * @since   4.0.0
     */
    public function getCoreUpdateNotification(QuickIconsEvent $event)
    {
        $context = $event->getContext();

        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_joomlaupdate')
        ) {
            return;
        }

        Text::script('PLG_QUICKICON_JOOMLAUPDATE_ERROR');
        Text::script('PLG_QUICKICON_JOOMLAUPDATE_UPDATEFOUND');
        Text::script('PLG_QUICKICON_JOOMLAUPDATE_UPTODATE');
        Text::script('MESSAGE');
        Text::script('ERROR');
        Text::script('INFO');
        Text::script('WARNING');

        $this->document->addScriptOptions(
            'js-joomla-update',
            [
                'url'     => Uri::base() . 'index.php?option=com_joomlaupdate',
                'ajaxUrl' => Uri::base() . 'index.php?option=com_joomlaupdate&task=update.ajax&'
                    . Session::getFormToken() . '=1',
                'version' => JVERSION,
            ]
        );

        $this->document->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_joomlaupdate', 'plg_quickicon_joomlaupdate/jupdatecheck.min.js', [], ['defer' => true], ['core']);

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = [
            [
                'link'  => 'index.php?option=com_joomlaupdate',
                'image' => 'icon-joomla',
                'icon'  => '',
                'text'  => Text::_('PLG_QUICKICON_JOOMLAUPDATE_CHECKING'),
                'id'    => 'plg_quickicon_joomlaupdate',
                'group' => 'MOD_QUICKICON_MAINTENANCE',
            ],
        ];

        $event->setArgument('result', $result);
    }
}
