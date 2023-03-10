<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_footer
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Footer\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_footer
 *
 * @since  __DEPLOY_VERSION__
 */
class FooterHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieve the copyright information.
     *
     * @param   SiteApplication  $app  The application object.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getList(SiteApplication $app): string
    {
        $date       = Factory::getDate();
        $cur_year   = HTMLHelper::_('date', $date, 'Y');
        $csite_name = $app->get('sitename');

        if (is_int(StringHelper::strpos(Text::_('MOD_FOOTER_LINE1'), '%date%'))) {
            $line1 = str_replace('%date%', $cur_year, Text::_('MOD_FOOTER_LINE1'));
        } else {
            $line1 = Text::_('MOD_FOOTER_LINE1');
        }

        if (is_int(StringHelper::strpos($line1, '%sitename%'))) {
            $lineone = str_replace('%sitename%', $csite_name, $line1);
        } else {
            $lineone = $line1;
        }

        return $lineone;
    }
}
