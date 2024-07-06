<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_community_info
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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

$lang         = $app->getLanguage();
$extension    = $app->getInput()->get('option');
$currentURL   = Uri::getInstance()->toString();

// Add language constants
CommunityInfoHelper::addText();
?>

<div id="CommunityInfo<?php echo strval($module->id); ?>" class="mod-community-info px-3">
  <p><?php echo Text::_('MOD_COMMUNITY_INFO_JOOMLA_DESC'); ?></p>
  <hr />
  <div class="info-block contact">
    <h3><?php echo Text::_('MOD_COMMUNITY_INFO_CONTACT_TITLE'); ?></h3>
    <p><?php echo CommunityInfoHelper::replaceText(Text::_('MOD_COMMUNITY_INFO_CONTACT_TEXT'), $links); ?></p>
  </div>
  <hr />
  <div class="info-block news">
    <div class="intro-txt">
      <div>
        <h3><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_TITLE'); ?></h3>
        <p><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_INTRO'); ?></p>
      </div>
      <a class="btn btn-primary btn-sm mt-1" href="<?php echo $links->get('newsletter'); ?>" target="_blank"><?php echo Text::_('MOD_COMMUNITY_INFO_NEWS_SUBSCRIBE'); ?></a>
      <button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNews" aria-expanded="false" aria-controls="collapseNews"><i class="icon-arrow-down"></i></button>
    </div>
    <table id="collapseNews" class="table community-info-news collapse">
      <tbody>
        <?php foreach ($news as $n => $article) : ?>
          <tr>
            <td scope="row"><a href="<?php echo $article->link; ?>" target="_blank"><?php echo $article->title; ?></a></td>
            <td style="text-align: right"><span class="small"><?php echo HTMLHelper::_('date', $article->pubDate, 'M j, Y'); ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>  
  <hr />
  <div class="info-block events">
    <div class="intro-txt">
      <div>
        <h3><?php echo Text::_('MOD_COMMUNITY_INFO_EVENTS_TITLE'); ?></h3>
        <p><?php echo Text::_('MOD_COMMUNITY_INFO_EVENTS_INTRO'); ?></p>
      </div>
      <button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEvents" aria-expanded="false" aria-controls="collapseEvents"><i class="icon-arrow-down"></i></button>
    </div>
    <table id="collapseEvents" class="table table-sm community-info-news collapse">
      <tbody>
        <?php foreach ($events as $e => $event) : ?>
          <tr>
            <td scope="row"><strong><a href="<?php echo $event->url; ?>" target="_blank"><?php echo $event->title; ?></a></strong><br /><span class="small"><?php echo $event->location; ?></span></td>
            <td style="text-align: right"><span class="small"><?php echo HTMLHelper::_('date', $event->start, 'D, M j, Y'); ?></span><br /><span class="small"><?php echo HTMLHelper::_('date', $event->start, 'H:i T'); ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <hr />
  <div class="info-block contribute">
    <a class="no-link" href="https://magazine.joomla.org/all-issues/june-2024/holopin-is-ready-to-launch,-claim-your-digital-badge" target="_blank"><img class="float-right" src="<?php echo Uri::root(true).'/media/mod_community_info/image/holopin-badge-board.png'; ?>" alt="joomla volunteer badge"></a>
    <h3><?php echo Text::_('MOD_COMMUNITY_INFO_CONTRIBUTE_TITLE'); ?></h3>
    <p><?php echo CommunityInfoHelper::replaceText(Text::_('MOD_COMMUNITY_INFO_CONTRIBUTE_TEXT'), $links); ?></p>
    <p><?php echo CommunityInfoHelper::replaceText(Text::_('MOD_COMMUNITY_INFO_CONTRIBUTE_CONTACT'), $links); ?></p>
  </div>
</div>

<template id="template-location-picker">
  <div class="select-location">
    <a href="#" onclick="openModal('location-modal', '<?php echo CommunityInfoHelper::getLocation($params, 'geolocation'); ?>')">
      <i class="icon-location"></i>
      <?php echo Text::_('MOD_COMMUNITY_INFO_CHOOSE_LOCATION'); ?>
    </a><span> (<?php echo Text::_('JCURRENT'); ?>: <?php echo CommunityInfoHelper::getLocation($params, 'label'); ?>)</span>
  </div>
</template>

<template id="template-location-modal-body">
  <form action="<?php echo $currentURL; ?>" method="post" enctype="multipart/form-data" name="adminForm" id="location-form" class="form-validate p-3" aria-label="<?php echo Text::_('MOD_COMMUNITY_INFO_CHOOSE_LOCATION'); ?>">
    <div class="row">
      <div class="col-12">
        <div class="input-group mb-3">
          <label for="locsearch" class="form-label">Search Location</label>
          <input id="locsearch" class="from-control" type="text" aria-label="Location search" aria-describedby="btn-locsearch">
          <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="btn-locsearch" onclick="searchLocation()">Search</button>
          </div>
        </div>
        <div id="locsearch_results" class="input-group mb-3"></div>
        <input id="module_task" class="hidden" type="hidden" name="module_task" value="">
        <input id="jform_lat" class="hidden" type="hidden" name="jform[lat]" value="<?php echo \trim($currentLoc[0]); ?>">
        <input id="jform_lng" class="hidden" type="hidden" name="jform[lng]" value="<?php echo \trim($currentLoc[1]); ?>">        
        <input id="jform_modid" class="hidden" type="hidden" name="jform[modid]" value="<?php echo $module->id; ?>">
        <input id="jform_autoloc" class="hidden" type="hidden" name="jform[autoloc]" value="<?php $params->get('auto_location', '1'); ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
      </div>
    </div>
  </form>
</template>

<?php
// Location form modal
$options = array('modal-dialog-scrollable' => true,
                  'title'  => Text::_('MOD_COMMUNITY_INFO_CHOOSE_LOCATION'),
                  'footer' => '<button onclick="autoLoc()" class="btn">'.Text::_('MOD_COMMUNITY_INFO_AUTO_LOCATION').'</button><button id="saveLocBtn" disabled onclick="saveLoc()" class="btn btn-primary">'.Text::_('MOD_COMMUNITY_INFO_SAVE_LOCATION').'</button>',
                );
echo HTMLHelper::_('bootstrap.renderModal', 'location-modal', $options, '<p>Loading...</p>');
?>

<script>
  async function callback(){
    // prepare location picker
    let moduleBody   = document.getElementById('CommunityInfo<?php echo strval($module->id); ?>');
    let moduleHeader = moduleBody.parentNode.previousElementSibling;
    moduleHeader.appendChild(document.getElementById('template-location-picker').content);

    // prepare modal
    document.getElementById('location-modal').classList.add('mod-community-info');

    // Send browsers current geolocation to com_ajax
    <?php if(intval($params->get('auto_location', 1))) : ?>
    try {
      let location = await getCurrentLocation();
      console.log('Current Location:', location);
      
      let response = await ajaxLocation(location, <?php echo $module->id; ?>, 'setLocation');
      console.log('Ajax Response:', Joomla.Text._(response));
    } catch (error) {
      console.error('Error:', error);
    }
    <?php endif; ?>
  }; //end callback

  if(document.readyState === 'complete' || (document.readyState !== 'loading' && !document.documentElement.doScroll)) {
    callback();
  } else {
    document.addEventListener('DOMContentLoaded', callback);
  }
</script>
