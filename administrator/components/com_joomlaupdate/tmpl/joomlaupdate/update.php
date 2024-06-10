<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useScript('com_joomlaupdate.default')
    ->useScript('bootstrap.popover');

$uploadLink = 'index.php?option=com_joomlaupdate&view=upload';

if (ComponentHelper::getParams('com_joomlaupdate')->get('chunked_download', 0) == 0) :
    $formURL = 'index.php?option=com_joomlaupdate&view=joomlaupdate'; //simple update
else :
    $formURL = 'index.php?option=com_joomlaupdate&view=update'; //chunked updates using ajax
endif;

$displayData = [
    'textPrefix' => 'COM_JOOMLAUPDATE_UPDATE',
    'title'      => Text::sprintf('COM_JOOMLAUPDATE_UPDATE_EMPTYSTATE_TITLE', $this->escape($this->updateInfo['latest'])),
    'content'    => Text::sprintf($this->langKey, $this->updateSourceKey),
    'formURL'    => $formURL,
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Updating_from_an_existing_version',
    'icon'       => 'icon-loop joomlaupdate',
    'createURL'  => '#'
];

if (isset($this->updateInfo['object']) && isset($this->updateInfo['object']->get('infourl')->_data)) :
    $displayData['content'] .= '<br>' . HTMLHelper::_(
        'link',
        $this->updateInfo['object']->get('infourl')->_data,
        Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'),
        [
            'target' => '_blank',
            'rel'    => 'noopener noreferrer',
            'title'  => isset($this->updateInfo['object']->get('infourl')->title) ? Text::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->get('infourl')->title) : ''
        ]
    );
endif;

// Confirm backup and check
$classVisibility = $this->noBackupCheck ? 'd-none' : '';
$checked = $this->noBackupCheck ? 'checked' : '';
$displayData['content'] .= '<div class="form-check d-flex justify-content-center ' . $classVisibility . '">
		<input class="form-check-input me-2" type="checkbox" value="" id="joomlaupdate-confirm-backup" ' . $checked . '>
		<label class="form-check-label" for="joomlaupdate-confirm-backup">
		' . Text::_('COM_JOOMLAUPDATE_UPDATE_CONFIRM_BACKUP') . '
		</label>
	</div>';

if ($this->getCurrentUser()->authorise('core.admin', 'com_joomlaupdate')) :
    $displayData['formAppend'] = '
        <div class="text-center"><a href="' . $uploadLink . '" class="btn btn-sm btn-outline-secondary">' . Text::_('COM_JOOMLAUPDATE_EMPTYSTATE_APPEND') . '</a></div>
        <input type="hidden" name="targetVersion" value="' . $this->updateInfo['latest'] . '" />
    ';
endif;

echo '<div id="joomlaupdate-wrapper">';

echo LayoutHelper::render('joomla.content.emptystate', $displayData);

echo '</div>';
