<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_languages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Languages\Site\Helper\LanguagesHelper;

$headerText      = $params->get('header_text');
$footerText      = $params->get('footer_text');
$list            = LanguagesHelper::getList($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require ModuleHelper::getLayoutPath('mod_languages', $params->get('layout', 'default'));
