<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.overridecheck
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Quickicon\OverrideCheck\Extension;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! template override notification plugin
 *
 * @since  4.0.0
 */
final class OverrideCheck extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onGetIcons' => 'onGetIcons',
        ];
    }

    /**
     * Returns an icon definition for an icon which looks for overrides update
     * via AJAX and displays a notification when such overrides are updated.
     *
     * @param   QuickIconsEvent  $event  The event object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onGetIcons(QuickIconsEvent $event): void
    {
        $context = $event->getContext();

        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->getApplication()->getIdentity()->authorise('core.manage', 'com_templates')
        ) {
            return;
        }

        $token    = Session::getFormToken() . '=1';
        $options  = [
            'url'      => Uri::base() . 'index.php?option=com_templates&view=templates',
            'ajaxUrl'  => Uri::base() . 'index.php?option=com_templates&view=templates&task=template.ajax&' . $token,
            'pluginId' => $this->getOverridePluginId(),
        ];

        $this->getApplication()->getDocument()->addScriptOptions('js-override-check', $options);

        Text::script('PLG_QUICKICON_OVERRIDECHECK_ERROR', true);
        Text::script('PLG_QUICKICON_OVERRIDECHECK_ERROR_ENABLE', true);
        Text::script('PLG_QUICKICON_OVERRIDECHECK_UPTODATE', true);
        Text::script('PLG_QUICKICON_OVERRIDECHECK_OVERRIDEFOUND', true);

        $this->getApplication()->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_overridecheck', 'plg_quickicon_overridecheck/overridecheck.js', [], ['defer' => true], ['core']);

        // Add the icon to the result array
        $result = $event->getArgument('result', []);

        $result[] = [
            [
                'link'  => 'index.php?option=com_templates&view=templates',
                'image' => 'icon-file',
                'icon'  => '',
                'text'  => $this->getApplication()->getLanguage()->_('PLG_QUICKICON_OVERRIDECHECK_CHECKING'),
                'id'    => 'plg_quickicon_overridecheck',
                'group' => 'MOD_QUICKICON_MAINTENANCE',
            ],
        ];

        $event->setArgument('result', $result);
    }

    /**
     * Gets the installer override plugin extension id.
     *
     * @return  integer  The installer override plugin extension id.
     *
     * @since   4.0.0
     */
    private function getOverridePluginId()
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('installer'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('override'));
        $db->setQuery($query);

        try {
            $result = (int) $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }
}
