<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView $this */

$uploadLink       = 'index.php?option=com_joomlaupdate&view=upload';
$reasonNoDownload = '';

if (!empty($this->reasonNoDownload)) {
    $reasonNoDownload = Text::_($this->reasonNoDownload) . '<br>';

    if (isset($this->detailsNoDownload->php)) {
        $reasonNoDownload .= Text::sprintf(
            'COM_JOOMLAUPDATE_NODOWNLOAD_EMPTYSTATE_REASON_PHP',
            $this->detailsNoDownload->php->used,
            $this->detailsNoDownload->php->required
        ) . '<br>';
    }

    if (isset($this->detailsNoDownload->db)) {
        $reasonNoDownload .= Text::sprintf(
            'COM_JOOMLAUPDATE_NODOWNLOAD_EMPTYSTATE_REASON_DATABASE',
            Text::_('JLIB_DB_SERVER_TYPE_' . $this->detailsNoDownload->db->type),
            $this->detailsNoDownload->db->used,
            $this->detailsNoDownload->db->required
        ) . '<br>';
    }

    $reasonNoDownload .= Text::_('COM_JOOMLAUPDATE_NODOWNLOAD_EMPTYSTATE_REASON_ACTION') . '<br>';
}

$displayData = [
    'textPrefix' => 'COM_JOOMLAUPDATE' . $this->messagePrefix,
    'content'    => $reasonNoDownload . Text::sprintf($this->langKey, $this->updateSourceKey),
    'formURL'    => 'index.php?option=com_joomlaupdate&view=joomlaupdate',
    'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Updating_from_an_existing_version',
    'icon'       => 'icon-loop joomlaupdate',
    'createURL'  => 'index.php?option=com_joomlaupdate&task=update.purge&' . Session::getFormToken() . '=1'
];

if ($this->getCurrentUser()->authorise('core.admin', 'com_joomlaupdate')) {
    $displayData['formAppend'] = '<div class="text-center"><a href="' . $uploadLink . '" class="btn btn-sm btn-outline-secondary">' . Text::_($displayData['textPrefix'] . '_EMPTYSTATE_APPEND') . '</a></div>';
}

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

$content = LayoutHelper::render('joomla.content.emptystate', $displayData);

// Inject Joomla! version
echo str_replace('%1$s', '&#x200E;' . $this->updateInfo['latest'], $content);
