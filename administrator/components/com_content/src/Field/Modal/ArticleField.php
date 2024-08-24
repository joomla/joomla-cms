<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal article picker.
 *
 * @since  1.6
 */
class ArticleField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Modal_Article';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value.
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   5.0.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Check if the value consist with id:alias, extract the id only
        if ($value && str_contains($value, ':')) {
            [$id]  = explode(':', $value, 2);
            $value = (int) $id;
        }

        $result = parent::setup($element, $value, $group);

        if (!$result) {
            return $result;
        }

        Factory::getApplication()->getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];

        // Prepare enabled actions
        $this->canDo['propagate']  = ((string) $this->element['propagate'] == 'true') && \count($languages) > 2;

        // Prepare Urls
        $linkArticles = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkArticles->setQuery([
            'option'                => 'com_content',
            'view'                  => 'articles',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            Session::getFormToken() => 1,
        ]);
        $linkArticle = clone $linkArticles;
        $linkArticle->setVar('view', 'article');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option'                => 'com_content',
            'task'                  => 'articles.checkin',
            'format'                => 'json',
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkArticles->setVar('forcedLanguage', $language);
            $linkArticle->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('COM_CONTENT_SELECT_AN_ARTICLE');
        }

        $urlSelect = $linkArticles;
        $urlEdit   = clone $linkArticle;
        $urlEdit->setVar('task', 'article.edit');
        $urlNew    = clone $linkArticle;
        $urlNew->setVar('task', 'article.add');

        $this->urls['select']  = (string) $urlSelect;
        $this->urls['new']     = (string) $urlNew;
        $this->urls['edit']    = (string) $urlEdit;
        $this->urls['checkin'] = (string) $linkCheckin;

        // Prepare titles
        $this->modalTitles['select']  = $modalTitle;
        $this->modalTitles['new']     = Text::_('COM_CONTENT_NEW_ARTICLE');
        $this->modalTitles['edit']    = Text::_('COM_CONTENT_EDIT_ARTICLE');

        $this->hint = $this->hint ?: Text::_('COM_CONTENT_SELECT_AN_ARTICLE');

        return $result;
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   5.0.0
     */
    protected function getValueTitle()
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__content'))
                    ->where($db->quoteName('id') . ' = :value')
                    ->bind(':value', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $title ?: $value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 5.0.0
     */
    protected function getLayoutData()
    {
        $data             = parent::getLayoutData();
        $data['language'] = (string) $this->element['language'];

        return $data;
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   5.0.0
     */
    protected function getRenderer($layoutId = 'default')
    {
        $layout = parent::getRenderer($layoutId);
        $layout->setComponent('com_content');
        $layout->setClient(1);

        return $layout;
    }
}
