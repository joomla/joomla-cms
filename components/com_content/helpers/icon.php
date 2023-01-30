<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component HTML Helper
 *
 * @since       1.5
 * @deprecated  5.0 Use the class \Joomla\Component\Content\Administrator\Service\HTML\Icon instead
 */
abstract class JHtmlIcon
{
    /**
     * Method to generate a link to the create item page for the given category
     *
     * @param   object    $category  The category information
     * @param   Registry  $params    The item parameters
     * @param   array     $attribs   Optional attributes for the link
     * @param   boolean   $legacy    True to use legacy images, false to use icomoon based graphic
     *
     * @return  string  The HTML markup for the create item link
     *
     * @deprecated 5.0 Use the class \Joomla\Component\Content\Administrator\Service\HTML\Icon instead
     */
    public static function create($category, $params, $attribs = [], $legacy = false)
    {
        return self::getIcon()->create($category, $params, $attribs, $legacy);
    }

    /**
     * Display an edit icon for the article.
     *
     * This icon will not display in a popup window, nor if the article is trashed.
     * Edit access checks must be performed in the calling code.
     *
     * @param   object    $article  The article information
     * @param   Registry  $params   The item parameters
     * @param   array     $attribs  Optional attributes for the link
     * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return  string  The HTML for the article edit icon.
     *
     * @since   1.6
     *
     * @deprecated 5.0 Use the class \Joomla\Component\Content\Administrator\Service\HTML\Icon instead
     */
    public static function edit($article, $params, $attribs = [], $legacy = false)
    {
        return self::getIcon()->edit($article, $params, $attribs, $legacy);
    }

    /**
     * Method to generate a popup link to print an article
     *
     * @param   object    $article  The article information
     * @param   Registry  $params   The item parameters
     * @param   array     $attribs  Optional attributes for the link
     * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return  string  The HTML markup for the popup link
     *
     * @deprecated 5.0 Use the class \Joomla\Component\Content\Administrator\Service\HTML\Icon instead
     */
    public static function print_popup($article, $params, $attribs = [], $legacy = false)
    {
        throw new \Exception(Text::_('COM_CONTENT_ERROR_PRINT_POPUP'));
    }

    /**
     * Method to generate a link to print an article
     *
     * @param   object    $article  Not used, @deprecated for 4.0
     * @param   Registry  $params   The item parameters
     * @param   array     $attribs  Not used, @deprecated for 4.0
     * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
     *
     * @return  string  The HTML markup for the popup link
     *
     * @deprecated 5.0 Use the class \Joomla\Component\Content\Administrator\Service\HTML\Icon instead
     */
    public static function print_screen($article, $params, $attribs = [], $legacy = false)
    {
        return self::getIcon()->print_screen($params, $legacy);
    }

    /**
     * Creates an icon instance.
     *
     * @return  \Joomla\Component\Content\Administrator\Service\HTML\Icon
     */
    private static function getIcon()
    {
        return (new \Joomla\Component\Content\Administrator\Service\HTML\Icon(Joomla\CMS\Factory::getApplication()));
    }
}
