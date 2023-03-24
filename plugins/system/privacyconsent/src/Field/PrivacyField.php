<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\PrivacyConsent\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides input for privacy
 *
 * @since  3.9.0
 */
class PrivacyField extends RadioField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.9.0
     */
    protected $type = 'privacy';

    /**
     * Method to get the field input markup.
     *
     * @return  string   The field input markup.
     *
     * @since   3.9.0
     */
    protected function getInput()
    {
        // Display the message before the field
        echo $this->getRenderer('plugins.system.privacyconsent.message')->render($this->getLayoutData());

        return parent::getInput();
    }

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.9.0
     */
    protected function getLabel()
    {
        if ($this->hidden) {
            return '';
        }

        return $this->getRenderer('plugins.system.privacyconsent.label')->render($this->getLayoutData());
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since   3.9.4
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $article        = false;
        $link           = false;
        $privacyArticle = $this->element['article'] > 0 ? (int) $this->element['article'] : 0;

        if ($privacyArticle && Factory::getApplication()->isClient('site')) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['id', 'alias', 'catid', 'language']))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $privacyArticle, ParameterType::INTEGER);
            $db->setQuery($query);
            $article = $db->loadObject();

            $slug           = $article->alias ? ($article->id . ':' . $article->alias) : $article->id;
            $article->link  = RouteHelper::getArticleRoute($slug, $article->catid, $article->language);
            $link           = $article->link;
        }

        $privacyMenuItem = $this->element['menu_item'] > 0 ? (int) $this->element['menu_item'] : 0;

        if ($privacyMenuItem && Factory::getApplication()->isClient('site')) {
            $link = 'index.php?Itemid=' . $privacyMenuItem;

            if (Multilanguage::isEnabled()) {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName(['id', 'language']))
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $privacyMenuItem, ParameterType::INTEGER);
                $db->setQuery($query);
                $menuItem = $db->loadObject();

                $link .= '&lang=' . $menuItem->language;
            }
        }

        $extraData = [
            'privacynote'          => !empty($this->element['note']) ? $this->element['note'] : Text::_('PLG_SYSTEM_PRIVACYCONSENT_NOTE_FIELD_DEFAULT'),
            'options'              => $this->getOptions(),
            'value'                => (string) $this->value,
            'translateLabel'       => $this->translateLabel,
            'translateDescription' => $this->translateDescription,
            'translateHint'        => $this->translateHint,
            'privacyArticle'       => $privacyArticle,
            'article'              => $article,
            'privacyLink'          => $link,
        ];

        return array_merge($data, $extraData);
    }
}
