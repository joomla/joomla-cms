<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\String\StringHelper;

/**
 * Admin Component Help Model
 *
 * @since  1.6
 */
class HelpModel extends BaseDatabaseModel
{
    /**
     * The search string
     *
     * @var    string
     * @since  1.6
     */
    protected $help_search = null;

    /**
     * The page to be viewed
     *
     * @var    string
     * @since  1.6
     */
    protected $page = null;

    /**
     * The ISO language tag
     *
     * @var    string
     * @since  1.6
     */
    protected $lang_tag = null;

    /**
     * Table of contents
     *
     * @var    array
     * @since  1.6
     */
    protected $toc = null;

    /**
     * URL for the latest version check
     *
     * @var    string
     * @since  1.6
     */
    protected $latest_version_check = null;

    /**
     * Method to get the help search string
     *
     * @return  string  Help search string
     *
     * @since   1.6
     */
    public function &getHelpSearch()
    {
        if (\is_null($this->help_search)) {
            $this->help_search = Factory::getApplication()->input->getString('helpsearch');
        }

        return $this->help_search;
    }

    /**
     * Method to get the page
     *
     * @return  string  The page
     *
     * @since   1.6
     */
    public function &getPage()
    {
        if (\is_null($this->page)) {
            $this->page = Help::createUrl(Factory::getApplication()->input->get('page', 'Start_Here'));
        }

        return $this->page;
    }

    /**
     * Method to get the lang tag
     *
     * @return  string  lang iso tag
     *
     * @since  1.6
     */
    public function getLangTag()
    {
        if (\is_null($this->lang_tag)) {
            $this->lang_tag = Factory::getLanguage()->getTag();

            if (!is_dir(JPATH_BASE . '/help/' . $this->lang_tag)) {
                // Use English as fallback
                $this->lang_tag = 'en-GB';
            }
        }

        return $this->lang_tag;
    }

    /**
     * Method to get the table of contents
     *
     * @return  array  Table of contents
     */
    public function &getToc()
    {
        if (!\is_null($this->toc)) {
            return $this->toc;
        }

        // Get vars
        $lang_tag    = $this->getLangTag();
        $help_search = $this->getHelpSearch();

        // New style - Check for a TOC \JSON file
        if (file_exists(JPATH_BASE . '/help/' . $lang_tag . '/toc.json')) {
            $data = json_decode(file_get_contents(JPATH_BASE . '/help/' . $lang_tag . '/toc.json'));

            // Loop through the data array
            foreach ($data as $key => $value) {
                $this->toc[$key] = Text::_('COM_ADMIN_HELP_' . $value);
            }

            // Sort the Table of Contents
            asort($this->toc);

            return $this->toc;
        }

        // Get Help files
        $files = Folder::files(JPATH_BASE . '/help/' . $lang_tag, '\.xml$|\.html$');
        $this->toc = array();

        foreach ($files as $file) {
            $buffer = file_get_contents(JPATH_BASE . '/help/' . $lang_tag . '/' . $file);

            if (!preg_match('#<title>(.*?)</title>#', $buffer, $m)) {
                continue;
            }

            $title = trim($m[1]);

            if (!$title) {
                continue;
            }

            // Translate the page title
            $title = Text::_($title);

            // Strip the extension
            $file = preg_replace('#\.xml$|\.html$#', '', $file);

            if ($help_search && StringHelper::strpos(StringHelper::strtolower(strip_tags($buffer)), StringHelper::strtolower($help_search)) === false) {
                continue;
            }

            // Add an item in the Table of Contents
            $this->toc[$file] = $title;
        }

        // Sort the Table of Contents
        asort($this->toc);

        return $this->toc;
    }
}
