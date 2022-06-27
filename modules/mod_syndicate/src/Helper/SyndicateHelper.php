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
     * @since   1.5
     */
    public static function getLink(Registry $params, HtmlDocument $document)
    {
        foreach ($document->_links as $link => $value) {
            $value = ArrayHelper::toString($value);

            if (strpos($value, 'application/' . $params->get('format') . '+xml')) {
                return $link;
            }
        }

        return null;
    }
}
