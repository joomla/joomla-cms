<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.contact
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Contact\Extension;

use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contact Plugin
 *
 * @since  3.2
 */
final class Contact extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * Plugin that retrieves contact information for contact
     *
     * @param   string   $context  The context of the content being passed to the plugin.
     * @param   mixed    &$row     An object with a "text" property
     * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
     * @param   integer  $page     Optional page number. Unused. Defaults to zero.
     *
     * @return  void
     */
    public function onContentPrepare($context, &$row, $params, $page = 0)
    {
        $allowed_contexts = ['com_content.category', 'com_content.article', 'com_content.featured'];

        if (!\in_array($context, $allowed_contexts)) {
            return;
        }

        // Return if we don't have valid params or don't link the author
        if (!($params instanceof Registry) || !$params->get('link_author')) {
            return;
        }

        // Return if an alias is used
        if ((int) $this->params->get('link_to_alias', 0) === 0 && $row->created_by_alias != '') {
            return;
        }

        // Return if we don't have a valid article id
        if (!isset($row->id) || !(int) $row->id) {
            return;
        }

        $contact = $this->getContactData($row->created_by);

        if ($contact === null) {
            return;
        }

        $row->contactid = $contact->contactid;
        $row->webpage   = $contact->webpage;
        $row->email     = $contact->email_to;
        $url            = $this->params->get('url', 'url');

        if ($row->contactid && $url === 'url') {
            $row->contact_link = Route::_(RouteHelper::getContactRoute($contact->contactid . ':' . $contact->alias, $contact->catid));
        } elseif ($row->webpage && $url === 'webpage') {
            $row->contact_link = $row->webpage;
        } elseif ($row->email && $url === 'email') {
            $row->contact_link = 'mailto:' . $row->email;
        } else {
            $row->contact_link = '';
        }
    }

    /**
     * Retrieve Contact
     *
     * @param   int  $userId  Id of the user who created the article
     *
     * @return  \stdClass|null  Object containing contact details or null if not found
     */
    private function getContactData($userId)
    {
        static $contacts = [];

        // Note: don't use isset() because value could be null.
        if (\array_key_exists($userId, $contacts)) {
            return $contacts[$userId];
        }

        $db     = $this->getDatabase();
        $query  = $db->getQuery(true);
        $userId = (int) $userId;

        $query->select($db->quoteName('contact.id', 'contactid'))
            ->select(
                $db->quoteName(
                    [
                        'contact.alias',
                        'contact.catid',
                        'contact.webpage',
                        'contact.email_to',
                    ]
                )
            )
            ->from($db->quoteName('#__contact_details', 'contact'))
            ->where(
                [
                    $db->quoteName('contact.published') . ' = 1',
                    $db->quoteName('contact.user_id') . ' = :createdby',
                ]
            )
            ->bind(':createdby', $userId, ParameterType::INTEGER);

        if (Multilanguage::isEnabled() === true) {
            $query->where(
                '(' . $db->quoteName('contact.language') . ' IN ('
                . implode(',', $query->bindArray([$this->getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING))
                . ') OR ' . $db->quoteName('contact.language') . ' IS NULL)'
            );
        }

        $query->order($db->quoteName('contact.id') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);

        $contacts[$userId] = $db->loadObject();

        return $contacts[$userId];
    }
}
