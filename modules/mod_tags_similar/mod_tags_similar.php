<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the tags_similar functions only once
JLoader::register('ModTagssimilarHelper', __DIR__ . '/helper.php');

$cacheparams = new stdClass;
$cacheparams->cachemode = 'safeuri';
$cacheparams->class = 'ModTagssimilarHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = array('id' => 'array', 'Itemid' => 'int');

$list = JModuleHelper::moduleCache($module, $params, $cacheparams);

if (!count($list))
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx', ''), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_tags_similar', $params->get('layout', 'default'));
