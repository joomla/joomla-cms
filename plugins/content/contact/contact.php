<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.Contact
 * @since       1.5
 */
class PlgContentContact extends JPlugin
{
	/**
	 * Plugin that retrieves contact information for contact
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    &$params  Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 */
	public function onContentPrepare($context, &$row, $params, $page = 0)
	{
        if ($context != 'com_content.category' && $context != 'com_content.article')
        {
            return true;
        }

        if ($params === null || $params === '')
        {
            return true;
        }

        if ((int) $params->get('link_author') == 0)
        {
            return true;
        }

        if (! isset($row->id) || (int) $row->id == 0)
        {
            return true;
        }

        $row->contactid = $this->getContactID($row->created_by);

        if (!empty($row->contactid))
        {
            $needle = 'index.php?option=com_contact&view=contact&id=' . $row->contactid;
            $menu = JFactory::getApplication()->getMenu();
            $item = $menu->getItems('link', $needle, true);
            $link = !empty($item) ? $needle . '&Itemid=' . $row->id : $needle;
            $row->contact_link = JRoute::_($link);
        }
        else
        {
            $row->contact_link = '';
        }

		return true;
	}

    /**
     * Retrieve Contact
     *
     * @param   int    $created_by
     * @param   int    $filter_language
     *
     * @return  mixed|null|integer
     */
    protected function getContactID($created_by)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->clear();

        $query->select('MAX(contact.id) AS contactid');
        $query->from('#__contact_details AS contact');
        $query->where('contact.published = 1');
        $query->where('contact.user_id = ' . (int) $created_by);

        if (JLanguageMultilang::isEnabled() == 1)
        {
            $query->where('(contact.language in '
                . '(' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') '
                . ' OR contact.language IS NULL)');
        }

        $db->setQuery($query->__toString());

        return $db->loadResult();
    }
}
