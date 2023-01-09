<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

if (empty($list)) {
    return;
}

$startLevel = reset($list)->getParent()->level;
$input      = $app->getInput();
$option     = $input->getCmd('option');
$view       = $input->getCmd('view');
$id         = $input->getInt('id');

?>
<ul class="mod-articlescategories categories-module mod-list">
<?php require ModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default') . '_items'); ?>
</ul>
