<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\CommunityInfo\Administrator\Helper\CommunityInfoHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.modal');
$wa->useScript('bootstrap.collapse');
$wa->useStyle('communityinfo.style');
$wa->useScript('communityinfo.script');
$wa->addInlineScript('window.', ['position' => 'after'], [], ['communityinfo.script']);

/** @var Joomla\CMS\Document\HtmlDocument $doc */
$doc = Factory::getApplication()->getDocument();
$doc->addScriptOptions('mod_community_info', ['debug' => (int) $app->get('debug', false)]);

$lang         = $app->getLanguage();
$extension    = $app->getInput()->get('option');
$currentURL   = Uri::getInstance()->toString();

// Add language constants
CommunityInfoHelper::addText();
?>

<div id="CommunityInfo<?php echo strval($module->id); ?>" class="mod-community-info px-3" data-autoloc="<?php echo $params->get('auto_location', '1'); ?>" data-cachetime="<?php echo $params->get('cache_time', '3'); ?>">
  <p><?php echo Text::_('MOD_COMMUNITY_INFO_JOOMLA_DESC'); ?></p>
  <hr>
  <div class="info-block contact">
    <h3><?php echo Text::_('MOD_COMMUNITY_INFO_CONTACT_TITLE'); ?></h3>
    <div id="contactTxt<?php echo strval($module->id); ?>" data-fetch-time="<?php echo $links_time; ?>">
      <p><?php echo CommunityInfoHelper::replaceText(Text::_('MOD_COMMUNITY_INFO_CONTACT_TEXT'), $links); ?></p>
    </div>
  </div>
  <hr>
  <div class="info-block news">
    <div class="intro-txt">
      <div>
        <h3><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_TITLE'); ?></h3>
        <p><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_INTRO'); ?></p>
      </div>
      <a class="btn btn-primary btn-sm mt-1" href="<?php echo $links->get('newsletter'); ?>" target="_blank"><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_SUBSCRIBE'); ?></a>
      <button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNews<?php echo strval($module->id); ?>" aria-expanded="false" aria-controls="collapseNews"><span class="icon-arrow-down" aria-hidden="true"></span></button>
    </div>
    <?php if (empty($news)) : ?>
      <div id="collapseNews<?php echo strval($module->id); ?>" class="community-info-news collapse">
        <div class="alert alert-info" role="alert">
          <?php echo Text::_('MOD_COMMUNITY_NO_NEWS_FOUND'); ?>
        </div>
      </div>
    <?php else : ?>
        <?php require ModuleHelper::getLayoutPath('mod_community_info', $params->get('layout', 'default') . '_news'); ?>
    <?php endif; ?>
  </div>
  <hr>
  <div class="info-block events">
    <div class="intro-txt">
      <div>
        <h3><?php echo Text::_('MOD_COMMUNITY_INFO_EVENTS_TITLE'); ?></h3>
        <p><?php echo Text::_('MOD_COMMUNITY_INFO_EVENTS_INTRO'); ?></p>
      </div>
      <button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvents<?php echo strval($module->id); ?>" aria-expanded="false" aria-controls="collapseEvents"><span class="icon-arrow-down" aria-hidden="true"></span></button>
    </div>
    <?php if (empty($events)) : ?>
      <div id="collapseEvents<?php echo strval($module->id); ?>" class="community-info-events collapse">
        <div class="alert alert-info" role="alert">
          <?php echo Text::_('MOD_COMMUNITY_NO_EVENTS_FOUND'); ?>
        </div>
      </div>
    <?php else : ?>
        <?php require ModuleHelper::getLayoutPath('mod_community_info', $params->get('layout', 'default') . '_events'); ?>
    <?php endif; ?>
  </div>
  <hr>
  <div class="info-block contribute">
    <a class="no-link" href="https://magazine.joomla.org/all-issues/june-2024/holopin-is-ready-to-launch,-claim-your-digital-badge" target="_blank"><img class="float-right" src="<?php echo Uri::root(true) . '/media/mod_community_info/images/holopin-badge-board.png'; ?>" alt="joomla volunteer badge"></a>
    <h3><?php echo Text::_('MOD_COMMUNITY_INFO_CONTRIBUTE_TITLE'); ?></h3>
    <div id="contributeTxt<?php echo strval($module->id); ?>">
      <p><?php echo CommunityInfoHelper::replaceText(Text::_('MOD_COMMUNITY_INFO_CONTRIBUTE_TEXT'), $links); ?></p>
      <p><?php echo CommunityInfoHelper::replaceText(Text::_('MOD_COMMUNITY_INFO_CONTRIBUTE_CONTACT'), $links); ?></p>
    </div>    
  </div>
</div>

<template id="template-location-picker">
  <div class="select-location">
    <a href="#" data-modal-id="location-modal<?php echo strval($module->id); ?>" data-geolocation="<?php echo $currentLoc; ?>">
      <span class="icon-location" aria-hidden="true"></span>
      <?php echo Text::_('MOD_COMMUNITY_INFO_CHOOSE_LOCATION'); ?>
    </a><span> (<?php echo Text::_('JCURRENT'); ?>: <?php echo $currentLocLabel; ?>)</span>
  </div>
</template>

<template id="template-location-modal<?php echo strval($module->id); ?>-body">
  <form action="<?php echo $currentURL; ?>" method="post" enctype="multipart/form-data" name="adminForm" id="location-form-<?php echo strval($module->id); ?>" class="form-validate p-3" aria-label="<?php echo Text::_('MOD_COMMUNITY_INFO_CHOOSE_LOCATION'); ?>">
    <div class="row">
      <div class="col-12">
        <div class="input-group mb-3">
          <label for="locsearch<?php echo strval($module->id); ?>" class="form-label">Search Location</label>
          <input id="locsearch<?php echo strval($module->id); ?>" class="from-control" type="text" aria-label="Location search" aria-describedby="btn-locsearch">
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="btn-locsearch<?php echo strval($module->id); ?>" onclick="searchLocation()">Search</button>
          </div>
        </div>
        <div id="locsearch_results<?php echo strval($module->id); ?>" class="input-group mb-3"></div>
        <input id="module_task<?php echo strval($module->id); ?>" class="hidden" type="hidden" name="module_task" value="">
        <input id="jform_lat<?php echo strval($module->id); ?>" class="hidden" type="hidden" name="jform[lat]" value="<?php echo \trim($currentLocArrary[0]); ?>">
        <input id="jform_lng<?php echo strval($module->id); ?>" class="hidden" type="hidden" name="jform[lng]" value="<?php echo \trim($currentLocArrary[1]); ?>">        
        <input id="jform_modid<?php echo strval($module->id); ?>" class="hidden" type="hidden" name="jform[modid]" value="<?php echo $module->id; ?>">
        <input id="jform_autoloc<?php echo strval($module->id); ?>" class="hidden" type="hidden" name="jform[autoloc]" value="<?php echo $params->get('auto_location', '1'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
      </div>
    </div>
  </form>
</template>

<?php
// Location form modal
$options = array('modal-dialog-scrollable' => true,
                  'title'  => Text::_('MOD_COMMUNITY_INFO_CHOOSE_LOCATION'),
                  'footer' => '<button id="btn-autoLoc' . strval($module->id) . '" class="btn">' . Text::_('MOD_COMMUNITY_INFO_AUTO_LOCATION') . '</button><button id="btn-saveLoc' . strval($module->id) . '" disabled class="btn btn-primary">' . Text::_('MOD_COMMUNITY_INFO_SAVE_LOCATION') . '</button>',
                );
echo HTMLHelper::_('bootstrap.renderModal', 'location-modal' . strval($module->id), $options, '<p>Loading...</p>');
?>
