<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Syndicate\Site\Helper;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_syndicate
 *
 * @since  1.5
 */
class SyndicateHelper
{
    /**
     * Gets the link
     *
     * @param   Registry      $params    The module parameters
     * @param   HtmlDocument  $document  The document
     *
     * @return  string|null  The link as a string, if found
     *
     * @since   5.1.0
     */
    public function getSyndicateLink(Registry $params, HtmlDocument $document)
    {
        foreach ($document->_links as $link => $value) {
            $value = ArrayHelper::toString($value);

            if (strpos($value, 'application/' . $params->get('format') . '+xml')) {
                return $link;
            }
        }

        return null;
    }

    /**
     * Gets the link
     *
     * @param   Registry      $params    The module parameters
     * @param   HtmlDocument  $document  The document
     *
     * @return  string|null  The link as a string, if found
     *
     * @since   1.5
     *
     * @deprecated 5.1.0 will be removed in 7.0
     *             Use the non-static method getSyndicateLink
     *             Example: Factory::getApplication()->bootModule('mod_syndicate', 'site')
     *                            ->getHelper('SyndicateHelper')
     *                            ->getSyndicateLink($params, Factory::getApplication()->getDocument())
     */
    public static function getLink(Registry $params, HtmlDocument $document)
    {
        return (new self())->getSyndicateLink($params, $document);
    }
}
