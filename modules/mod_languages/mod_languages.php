<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Languages\Site\Helper\LanguagesHelper;

$headerText = $params->get('header_text');
$footerText = $params->get('footer_text');
$list       = LanguagesHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_languages', $params->get('layout', 'default'));
