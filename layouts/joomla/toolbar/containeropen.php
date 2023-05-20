<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->registerAndUseScript('joomla.toolbar', 'legacy/toolbar.min.js', [], ['defer' => true], ['core']);

?>
<nav aria-label="<?php echo Text::_('JTOOLBAR'); ?>">
<div class="btn-toolbar d-flex" role="toolbar" id="<?php echo $displayData['id']; ?>">
