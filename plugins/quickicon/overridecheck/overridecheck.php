<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Overridecheck
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! template override notification plugin
 *
 * @since  4.0.0
 */
class PlgQuickiconOverrideCheck extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.7.0
     */
    protected $app;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseInterface
     *
     * @since  3.8.0
     */
    protected $db;

    /**
     * Returns an icon definition for an icon which looks for overrides update
     * via AJAX and displays a notification when such overrides are updated.
     *
     * @param   string  $context  The calling context
     *
     * @return  array  A list of icon definition associative arrays, consisting of the
     *                 keys link, image, text and access.
     *
     * @since   4.0.0
     */
    public function onGetIcons($context)
    {
        if ($context !== $this->params->get('context', 'update_quickicon') || !$this->app->getIdentity()->authorise('core.manage', 'com_templates')) {
            return [];
        }

        $token    = Session::getFormToken() . '=1';
        $options  = [
            'url'      => Uri::base() . 'index.php?option=com_templates&view=templates',
            'ajaxUrl'  => Uri::base() . 'index.php?option=com_templates&view=templates&task=template.ajax&' . $token,
            'pluginId' => $this->getOverridePluginId(),
        ];

        $this->app->getDocument()->addScriptOptions('js-override-check', $options);

        Text::script('PLG_QUICKICON_OVERRIDECHECK_ERROR', true);
        Text::script('PLG_QUICKICON_OVERRIDECHECK_ERROR_ENABLE', true);
        Text::script('PLG_QUICKICON_OVERRIDECHECK_UPTODATE', true);
        Text::script('PLG_QUICKICON_OVERRIDECHECK_OVERRIDEFOUND', true);

        $this->app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_overridecheck', 'plg_quickicon_overridecheck/overridecheck.js', [], ['defer' => true], ['core']);

        return [
            [
                'link'  => 'index.php?option=com_templates&view=templates',
                'image' => 'icon-file',
                'icon'  => '',
                'text'  => Text::_('PLG_QUICKICON_OVERRIDECHECK_CHECKING'),
                'id'    => 'plg_quickicon_overridecheck',
                'group' => 'MOD_QUICKICON_MAINTENANCE',
            ],
        ];
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
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('extension_id'))
            ->from($this->db->quoteName('#__extensions'))
            ->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('installer'))
            ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('override'));
        $this->db->setQuery($query);

        try {
            $result = (int) $this->db->loadResult();
        } catch (\RuntimeException $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }
}
