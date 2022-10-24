<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.jooa11y
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Jooa11y plugin to add an accessibility checker
 *
 * @since  4.1.0
 */
class PlgSystemJooa11y extends CMSPlugin implements SubscriberInterface
{
    /**
     * Application object.
     *
     * @var    CMSApplicationInterface
     * @since  4.1.0
     */
    protected $app;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * Subscribe to certain events
     *
     * @return string[]  An array of event mappings
     *
     * @since 4.1.0
     *
     * @throws Exception
     */
    public static function getSubscribedEvents(): array
    {
        $mapping = [];

        // Only trigger in frontend
        if (Factory::getApplication()->isClient('site')) {
            $mapping['onBeforeCompileHead'] = 'initJooa11y';
        }

        return $mapping;
    }

    /**
     * Method to check if the current user is allowed to see the debug information or not.
     *
     * @return  boolean  True if access is allowed.
     *
     * @since   4.1.0
     */
    private function isAuthorisedDisplayChecker(): bool
    {
        static $result;

        if (is_bool($result)) {
            return $result;
        }

        // If the user is not allowed to view the output then end here.
        $filterGroups = (array) $this->params->get('filter_groups', []);

        if (!empty($filterGroups)) {
            $userGroups = $this->app->getIdentity()->get('groups');

            if (!array_intersect($filterGroups, $userGroups)) {
                $result = false;

                return $result;
            }
        }

        $result = true;

        return $result;
    }

    /**
     * Add the checker.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function initJooa11y()
    {
        // Check if we are in a preview modal or the plugin has enforced loading
        $showJooa11y = $this->app->getInput()->get('jooa11y', $this->params->get('showAlways', 0));

        // Load the checker if authorised
        if (!$showJooa11y || !$this->isAuthorisedDisplayChecker()) {
            return;
        }

        // Get the document object.
        $document = $this->app->getDocument();

        // Determine if it is an LTR or RTL language
        $direction = Factory::getLanguage()->isRtl() ? 'right' : 'left';

        // Detect the current active language
        $lang = Factory::getLanguage()->getTag();

        // Add plugin settings from the xml
        $document->addScriptOptions(
            'jooa11yOptions',
            [
                'checkRoot'       => $this->params->get('checkRoot', 'main'),
                'readabilityRoot' => $this->params->get('readabilityRoot', 'main'),
                'containerIgnore' => $this->params->get('containerIgnore'),
            ]
        );

        // Add the language constants
        $constants = [
            'PLG_SYSTEM_JOOA11Y_ALERT_CLOSE',
            'PLG_SYSTEM_JOOA11Y_ALERT_TEXT',
            'PLG_SYSTEM_JOOA11Y_AVG_WORD_PER_SENTENCE',
            'PLG_SYSTEM_JOOA11Y_COMPLEX_WORDS',
            'PLG_SYSTEM_JOOA11Y_CONTAINER_LABEL',
            'PLG_SYSTEM_JOOA11Y_CONTRAST',
            'PLG_SYSTEM_JOOA11Y_CONTRAST_ERROR_INPUT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_CONTRAST_ERROR_INPUT_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_CONTRAST_ERROR_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_CONTRAST_ERROR_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_CONTRAST_WARNING_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_CONTRAST_WARNING_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_DARK_MODE',
            'PLG_SYSTEM_JOOA11Y_DIFFICULT_READABILITY',
            'PLG_SYSTEM_JOOA11Y_EMBED_AUDIO',
            'PLG_SYSTEM_JOOA11Y_EMBED_GENERAL_WARNING',
            'PLG_SYSTEM_JOOA11Y_EMBED_MISSING_TITLE',
            'PLG_SYSTEM_JOOA11Y_EMBED_VIDEO',
            'PLG_SYSTEM_JOOA11Y_ERROR',
            'PLG_SYSTEM_JOOA11Y_FAIRLY_DIFFICULT_READABILITY',
            'PLG_SYSTEM_JOOA11Y_FILE_TYPE_WARNING',
            'PLG_SYSTEM_JOOA11Y_FILE_TYPE_WARNING_TIP',
            'PLG_SYSTEM_JOOA11Y_FORM_LABELS',
            'PLG_SYSTEM_JOOA11Y_GOOD',
            'PLG_SYSTEM_JOOA11Y_GOOD_READABILITY',
            'PLG_SYSTEM_JOOA11Y_HEADING_EMPTY',
            'PLG_SYSTEM_JOOA11Y_HEADING_EMPTY_WITH_IMAGE',
            'PLG_SYSTEM_JOOA11Y_HEADING_FIRST',
            'PLG_SYSTEM_JOOA11Y_HEADING_LONG',
            'PLG_SYSTEM_JOOA11Y_HEADING_LONG_INFO',
            'PLG_SYSTEM_JOOA11Y_HEADING_MISSING_ONE',
            'PLG_SYSTEM_JOOA11Y_HEADING_NON_CONSECUTIVE_LEVEL',
            'PLG_SYSTEM_JOOA11Y_HIDE_OUTLINE',
            'PLG_SYSTEM_JOOA11Y_HIDE_SETTINGS',
            'PLG_SYSTEM_JOOA11Y_HYPERLINK_ALT_LENGTH_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_HYPERLINK_ALT_LENGTH_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_IMAGE_FIGURE_DECORATIVE',
            'PLG_SYSTEM_JOOA11Y_IMAGE_FIGURE_DECORATIVE_INFO',
            'PLG_SYSTEM_JOOA11Y_IMAGE_FIGURE_DUPLICATE_ALT',
            'PLG_SYSTEM_JOOA11Y_LABELS_ARIA_LABEL_INPUT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LABELS_ARIA_LABEL_INPUT_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LABELS_INPUT_RESET_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LABELS_INPUT_RESET_MESSAGE_TIP',
            'PLG_SYSTEM_JOOA11Y_LABELS_MISSING_IMAGE_INPUT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LABELS_MISSING_LABEL_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LABELS_NO_FOR_ATTRIBUTE_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LABELS_NO_FOR_ATTRIBUTE_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LANG_CODE',
            'PLG_SYSTEM_JOOA11Y_LINKS_ADVANCED',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_HAS_BAD_WORD_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_HAS_BAD_WORD_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_HAS_SUS_WORD_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_HAS_SUS_WORD_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_PLACEHOLDER_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_TOO_LONG_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_ALT_TOO_LONG_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_ANCHOR_LINK_AND_ALT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_ANCHOR_LINK_AND_ALT_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_BEST_PRACTICES',
            'PLG_SYSTEM_JOOA11Y_LINK_BEST_PRACTICES_DETAILS',
            'PLG_SYSTEM_JOOA11Y_LINK_DECORATIVE_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_EMPTY',
            'PLG_SYSTEM_JOOA11Y_LINK_EMPTY_LINK_NO_LABEL',
            'PLG_SYSTEM_JOOA11Y_LINK_HYPERLINKED_IMAGE_ARIA_HIDDEN',
            'PLG_SYSTEM_JOOA11Y_LINK_IDENTICAL_NAME',
            'PLG_SYSTEM_JOOA11Y_LINK_IDENTICAL_NAME_TIP',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_BAD_ALT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_BAD_ALT_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_LINK_ALT_TEXT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_LINK_ALT_TEXT_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_LINK_NULL_ALT_NO_TEXT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_PLACEHOLDER_ALT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_SUS_ALT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_IMAGE_SUS_ALT_MESSAGE_INFO',
            'PLG_SYSTEM_JOOA11Y_LINK_LABEL',
            'PLG_SYSTEM_JOOA11Y_LINK_LINK_HAS_ALT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_LINK_PASS_ALT',
            'PLG_SYSTEM_JOOA11Y_LINK_STOPWORD',
            'PLG_SYSTEM_JOOA11Y_LINK_STOPWORD_TIP',
            'PLG_SYSTEM_JOOA11Y_LINK_URL',
            'PLG_SYSTEM_JOOA11Y_LINK_URL_TIP',
            'PLG_SYSTEM_JOOA11Y_MAIN_TOGGLE_LABEL',
            'PLG_SYSTEM_JOOA11Y_MISSING_ALT_LINK_BUT_HAS_TEXT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_MISSING_ALT_LINK_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_MISSING_ALT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_NEW_TAB_WARNING',
            'PLG_SYSTEM_JOOA11Y_NEW_TAB_WARNING_TIP',
            'PLG_SYSTEM_JOOA11Y_OFF',
            'PLG_SYSTEM_JOOA11Y_ON',
            'PLG_SYSTEM_JOOA11Y_PAGE_OUTLINE',
            'PLG_SYSTEM_JOOA11Y_PANEL_HEADING_MISSING_ONE',
            'PLG_SYSTEM_JOOA11Y_PANEL_STATUS_BOTH',
            'PLG_SYSTEM_JOOA11Y_PANEL_STATUS_ERRORS',
            'PLG_SYSTEM_JOOA11Y_PANEL_STATUS_HIDDEN',
            'PLG_SYSTEM_JOOA11Y_PANEL_STATUS_ICON',
            'PLG_SYSTEM_JOOA11Y_PANEL_STATUS_NONE',
            'PLG_SYSTEM_JOOA11Y_PANEL_STATUS_WARNINGS',
            'PLG_SYSTEM_JOOA11Y_QA_BAD_ITALICS',
            'PLG_SYSTEM_JOOA11Y_QA_BAD_LINK',
            'PLG_SYSTEM_JOOA11Y_QA_BLOCKQUOTE_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_QA_BLOCKQUOTE_MESSAGE_TIP',
            'PLG_SYSTEM_JOOA11Y_QA_DUPLICATE_ID',
            'PLG_SYSTEM_JOOA11Y_QA_DUPLICATE_ID_TIP',
            'PLG_SYSTEM_JOOA11Y_QA_FAKE_HEADING',
            'PLG_SYSTEM_JOOA11Y_QA_FAKE_HEADING_INFO',
            'PLG_SYSTEM_JOOA11Y_QA_PAGE_LANGUAGE_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_QA_PDF_COUNT',
            'PLG_SYSTEM_JOOA11Y_QA_SHOULD_BE_LIST',
            'PLG_SYSTEM_JOOA11Y_QA_SHOULD_BE_LIST_TIP',
            'PLG_SYSTEM_JOOA11Y_QA_UPPERCASE_WARNING',
            'PLG_SYSTEM_JOOA11Y_READABILITY',
            'PLG_SYSTEM_JOOA11Y_READABILITY_NOT_ENOUGH_CONTENT_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_READABILITY_NO_P_OR_LI_MESSAGE',
            'PLG_SYSTEM_JOOA11Y_SETTINGS',
            'PLG_SYSTEM_JOOA11Y_SHORTCUT_SR',
            'PLG_SYSTEM_JOOA11Y_SHORTCUT_TOOLTIP',
            'PLG_SYSTEM_JOOA11Y_SHOW_OUTLINE',
            'PLG_SYSTEM_JOOA11Y_SHOW_SETTINGS',
            'PLG_SYSTEM_JOOA11Y_TABLES_EMPTY_HEADING',
            'PLG_SYSTEM_JOOA11Y_TABLES_EMPTY_HEADING_INFO',
            'PLG_SYSTEM_JOOA11Y_TABLES_MISSING_HEADINGS',
            'PLG_SYSTEM_JOOA11Y_TABLES_MISSING_HEADINGS_INFO',
            'PLG_SYSTEM_JOOA11Y_TABLES_SEMANTIC_HEADING',
            'PLG_SYSTEM_JOOA11Y_TABLES_SEMANTIC_HEADING_INFO',
            'PLG_SYSTEM_JOOA11Y_TEXT_UNDERLINE_WARNING',
            'PLG_SYSTEM_JOOA11Y_TEXT_UNDERLINE_WARNING_TIP',
            'PLG_SYSTEM_JOOA11Y_TOTAL_WORDS',
            'PLG_SYSTEM_JOOA11Y_VERY_DIFFICULT_READABILITY',
            'PLG_SYSTEM_JOOA11Y_WARNING',
        ];

        foreach ($constants as $constant) {
            Text::script($constant);
        }

        /** @var Joomla\CMS\WebAsset\WebAssetManager $wa*/
        $wa = $document->getWebAssetManager();

        $wa->getRegistry()->addRegistryFile('media/plg_system_jooa11y/joomla.asset.json');

        $wa->useScript('plg_system_jooa11y.jooa11y')
            ->useStyle('plg_system_jooa11y.jooa11y');

        return true;
    }
}
