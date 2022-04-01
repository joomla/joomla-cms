<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the archive functions only once
JLoader::register('ModArchiveHelper', __DIR__ . '/helper.php');

$params->def('count', 10);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');
$list            = ModArchiveHelper::getList($params);

require JModuleHelper::getLayoutPath('mod_articles_archive', $params->get('layout', 'default'));
