<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.skipto
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Skipto\Extension;

use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Skipto plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  4.0.0
 */
final class Skipto extends CMSPlugin
{
    /**
     * Add the skipto navigation menu.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAfterDispatch()
    {
        $section = $this->params->get('section', 'administrator');

        if ($section !== 'both' && $this->getApplication()->isClient($section) !== true) {
            return;
        }

        // Get the document object.
        $document = $this->getApplication()->getDocument();

        if ($document->getType() !== 'html') {
            return;
        }

        // Are we in a modal?
        if ($this->getApplication()->getInput()->get('tmpl', '', 'cmd') === 'component') {
            return;
        }

        // Load language file.
        $this->loadLanguage();

        // Add plugin settings and strings for translations in JavaScript.
        $document->addScriptOptions(
            'skipto-settings',
            [
                'settings' => [
                    'skipTo' => [
                        // Feature switches
                        'enableActions'               => false,
                        'enableHeadingLevelShortcuts' => false,

                        // Customization of button and menu
                        'accesskey'     => '9',
                        'displayOption' => 'popup',

                        // Button labels and messages
                        'buttonLabel'            => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_TITLE'),
                        'buttonTooltipAccesskey' => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_ACCESS_KEY'),

                        // Menu labels and messages
                        'landmarkGroupLabel'  => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK'),
                        'headingGroupLabel'   => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_HEADING'),
                        'mofnGroupLabel'      => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_HEADING_MOFN'),
                        'headingLevelLabel'   => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_HEADING_LEVEL'),
                        'mainLabel'           => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_MAIN'),
                        'searchLabel'         => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_SEARCH'),
                        'navLabel'            => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_NAV'),
                        'regionLabel'         => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_REGION'),
                        'asideLabel'          => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_ASIDE'),
                        'footerLabel'         => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_FOOTER'),
                        'headerLabel'         => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_HEADER'),
                        'formLabel'           => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_FORM'),
                        'msgNoLandmarksFound' => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_LANDMARK_NONE'),
                        'msgNoHeadingsFound'  => $this->getApplication()->getLanguage()->_('PLG_SYSTEM_SKIPTO_HEADING_NONE'),

                        // Selectors for landmark and headings sections
                        'headings'  => 'h1, h2, h3',
                        'landmarks' => 'main, nav, search, aside, header, footer, form',
                    ],
                ],
            ]
        );

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $document->getWebAssetManager();
        $wa->useScript('skipto');
    }
}
