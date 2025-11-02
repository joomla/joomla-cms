<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Languages\Administrator\View\Multilangstatus\HtmlView $this */

$notice_disabled  = !$this->language_filter && ($this->homes > 1 || $this->switchers != 0);
$notice_switchers = !$this->switchers && ($this->homes > 1 || $this->language_filter);

// Defining arrays
$content_languages = array_column($this->contentlangs, 'lang_code');
$sitelangs         = array_column($this->site_langs, 'element');
$home_pages        = array_column($this->homepages, 'language');
?>
<div class="mod-multilangstatus">
    <?php if (!$this->language_filter && $this->switchers == 0) : ?>
        <?php if ($this->homes == 1) : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_NONE'); ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info">
                <span class="icon-info-circle" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_USELESS_HOMES'); ?>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <?php if (!in_array($this->default_lang, $content_languages)) : ?>
            <div class="alert alert-error">
                <span class="icon-exclamation" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('ERROR'); ?></span>
                <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_ERROR_DEFAULT_CONTENT_LANGUAGE', $this->default_lang); ?>
            </div>
        <?php else : ?>
            <?php foreach ($this->contentlangs as $contentlang) : ?>
                <?php if ($contentlang->lang_code == $this->default_lang && $contentlang->published != 1) : ?>
                    <div class="alert alert-error">
                        <span class="icon-exclamation" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('ERROR'); ?></span>
                        <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_ERROR_DEFAULT_CONTENT_LANGUAGE', $this->default_lang); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php foreach ($this->statuses as $status) : ?>
            <?php // Displays error when Site language and Content language are published but Home page is unpublished, trashed or missing. ?>
            <?php if ($status->lang_code && $status->published == 1 && $status->home_published != 1) : ?>
                <div class="alert alert-warning">
                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_HOME_UNPUBLISHED', $status->lang_code, $status->lang_code); ?>
                </div>
            <?php endif; ?>
            <?php // Displays error when both Content Language and Home page are unpublished. ?>
            <?php if ($status->lang_code && $status->published == 0 && $status->home_published != 1) : ?>
                <div class="alert alert-warning">
                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_LANGUAGE_HOME_UNPUBLISHED', $status->lang_code, $status->lang_code); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($notice_disabled) : ?>
            <div class="alert alert-warning">
                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_LANGUAGEFILTER_DISABLED'); ?>
            </div>
        <?php endif; ?>
        <?php if ($notice_switchers) : ?>
            <div class="alert alert-warning">
                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_LANGSWITCHER_UNPUBLISHED'); ?>
            </div>
        <?php endif; ?>
        <?php foreach ($this->contentlangs as $contentlang) : ?>
            <?php if (array_key_exists($contentlang->lang_code, $this->homepages) && (!array_key_exists($contentlang->lang_code, $this->site_langs) || $contentlang->published != 1)) : ?>
                <div class="alert alert-warning">
                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_ERROR_CONTENT_LANGUAGE', $contentlang->lang_code); ?>
                </div>
            <?php endif; ?>
            <?php if (!array_key_exists($contentlang->lang_code, $this->site_langs)) : ?>
                <div class="alert alert-warning">
                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_ERROR_LANGUAGE_TAG', $contentlang->lang_code); ?>
                </div>
            <?php endif; ?>
            <?php if ($contentlang->published == -2) : ?>
                <div class="alert alert-warning">
                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_LANGUAGE_TRASHED', $contentlang->lang_code); ?>
                </div>
            <?php endif; ?>
            <?php if (empty($contentlang->sef)) : ?>
                <div class="alert alert-error">
                    <span class="icon-exclamation" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('ERROR'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_LANGUAGE_SEF_MISSING', $contentlang->lang_code); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($this->listUsersError) : ?>
            <div class="alert alert-warning">
                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_CONTACTS_ERROR_TIP'); ?>
                <ul>
                <?php foreach ($this->listUsersError as $user) : ?>
                    <li>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_CONTACTS_ERROR', $user->name); ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php // Displays error when the Content Language has been deleted ?>
        <?php foreach ($sitelangs as $sitelang) : ?>
            <?php if (!in_array($sitelang, $content_languages) && in_array($sitelang, $home_pages)) : ?>
                <div class="alert alert-warning">
                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                    <?php echo Text::sprintf('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_LANGUAGE_MISSING', $sitelang); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <table class="table table-sm">
            <caption class="visually-hidden"><?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_TABLE_CAPTION'); ?></caption>
            <thead>
                <tr>
                    <th scope="col">
                        <?php echo Text::_('JDETAILS'); ?>
                    </th>
                    <th class="text-center" scope="col">
                        <?php echo Text::_('JSTATUS'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">
                        <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_LANGUAGEFILTER'); ?>
                    </th>
                    <td class="text-center">
                        <?php if ($this->language_filter) : ?>
                            <?php echo Text::_('JENABLED'); ?>
                        <?php else : ?>
                            <?php echo Text::_('JDISABLED'); ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_LANGSWITCHER_PUBLISHED'); ?>
                    </th>
                    <td class="text-center">
                        <?php if ($this->switchers != 0) : ?>
                            <?php echo $this->switchers; ?>
                        <?php else : ?>
                            <?php echo Text::_('JNONE'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php if ($this->homes > 1) : ?>
                            <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED_INCLUDING_ALL'); ?>
                        <?php else : ?>
                            <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED'); ?>
                        <?php endif; ?>
                    </th>
                    <td class="text-center">
                        <?php if ($this->homes > 1) : ?>
                            <?php echo $this->homes; ?>
                        <?php else : ?>
                            <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED_ALL'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="table table-sm">
            <caption class="visually-hidden"><?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_TABLE_CAPTION'); ?></caption>
            <thead>
                <tr>
                    <th scope="col">
                        <?php echo Text::_('JGRID_HEADING_LANGUAGE'); ?>
                    </th>
                    <th class="text-center" scope="col">
                        <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_SITE_LANG_PUBLISHED'); ?>
                    </th>
                    <th class="text-center" scope="col">
                        <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_CONTENT_LANGUAGE_PUBLISHED'); ?>
                    </th>
                    <th class="text-center" scope="col">
                        <?php echo Text::_('COM_LANGUAGES_MULTILANGSTATUS_HOMES_PUBLISHED'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->statuses as $status) : ?>
                    <?php if ($status->element) : ?>
                        <tr>
                            <th scope="row">
                                <?php echo $status->element; ?>
                            </th>
                    <?php endif; ?>
                    <?php // Published Site languages ?>
                    <?php if ($status->element) : ?>
                            <td class="text-center">
                                <span class="text-success icon-check" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                            </td>
                    <?php else : ?>
                            <td class="text-center">
                                <?php echo Text::_('JNO'); ?>
                            </td>
                    <?php endif; ?>
                    <?php // Published Content languages ?>
                        <td class="text-center">
                            <?php if ($status->lang_code && $status->published == 1) : ?>
                                <span class="text-success icon-check" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                            <?php elseif ($status->lang_code && $status->published == 0) : ?>
                                <span class="text-danger icon-times" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            <?php elseif ($status->lang_code && $status->published == -2) : ?>
                                <span class="icon-trash" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            <?php else : ?>
                                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            <?php endif; ?>
                        </td>
                    <?php // Published Home pages ?>
                        <td class="text-center">
                            <?php if ($status->home_published == 1) : ?>
                                <span class="text-success icon-check" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                            <?php elseif ($status->home_published == 0) : ?>
                                <span class="text-danger icon-times" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('JNO'); ?></span>
                            <?php elseif ($status->home_published == -2) : ?>
                                <span class="icon-trash" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            <?php else : ?>
                                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($this->contentlangs as $contentlang) : ?>
                    <?php if (!array_key_exists($contentlang->lang_code, $this->site_langs)) : ?>
                        <tr>
                            <th scope="row">
                                <?php echo $contentlang->lang_code; ?>
                            </th>
                            <td class="text-center">
                                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($contentlang->published == 1) : ?>
                                    <span class="text-success icon-check" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                                <?php elseif ($contentlang->published == 0 && array_key_exists($contentlang->lang_code, $this->homepages)) : ?>
                                    <span class="text-danger icon-times" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('JNO'); ?></span>
                                <?php elseif ($contentlang->published == -2 && array_key_exists($contentlang->lang_code, $this->homepages)) : ?>
                                    <span class="icon-trash" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (!array_key_exists($contentlang->lang_code, $this->homepages)) : ?>
                                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                                <?php else : ?>
                                    <span class="text-success icon-check" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php // Display error when the Content Language has been deleted ?>
                <?php foreach ($sitelangs as $sitelang) : ?>
                    <?php if (!in_array($sitelang, $content_languages) && in_array($sitelang, $home_pages)) : ?>
                        <tr>
                            <th scope="row">
                                <?php echo $sitelang; ?>
                            </th>
                            <td class="text-center">
                                <span class="text-success icon-check" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="text-success icon-check" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('JYES'); ?></span>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
