<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Users\Site\View\Captive\HtmlView;

/** @var HtmlView $this */

$shownMethods = [];

?>
<div id="com-users-select">
    <h2 id="com-users-select-heading">
        <?php echo Text::_('COM_USERS_MFA_SELECT_PAGE_HEAD'); ?>
    </h2>
    <div id="com-users-select-information">
        <p>
            <?php echo Text::_('COM_USERS_LBL_SELECT_INSTRUCTIONS'); ?>
        </p>
    </div>

    <div class="com-users-select-methods p-2">
        <?php foreach ($this->records as $record) :
            if (!array_key_exists($record->method, $this->mfaMethods) && ($record->method != 'backupcodes')) {
                continue;
            }

            $allowEntryBatching = isset($this->mfaMethods[$record->method]) ? $this->mfaMethods[$record->method]['allowEntryBatching'] : false;

            if ($this->allowEntryBatching) {
                if ($allowEntryBatching && in_array($record->method, $shownMethods)) {
                    continue;
                }
                $shownMethods[] = $record->method;
            }

            $methodName = $this->getModel()->translateMethodName($record->method);
            ?>
        <a class="com-users-method p-2 border-top border-dark bg-light d-flex flex-row flex-wrap justify-content-start align-items-center text-decoration-none gap-2 text-body"
           href="<?php echo Route::_('index.php?option=com_users&view=captive&record_id=' . $record->id)?>">
            <img src="<?php echo Uri::root() . $this->getModel()->getMethodImage($record->method) ?>"
                 alt="<?php echo $this->escape(strip_tags((string) $record->title)) ?>"
                 class="com-users-method-image img-fluid" />
            <?php if (!$this->allowEntryBatching || !$allowEntryBatching) : ?>
                <span class="com-users-method-title flex-grow-1 fs-5 fw-bold">
                    <?php if ($record->method === 'backupcodes') : ?>
                        <?php echo $record->title ?>
                    <?php else : ?>
                        <?php echo $this->escape($record->title) ?>
                    <?php endif; ?>
                </span>
                <small class="com-users-method-name text-muted">
                    <?php echo $methodName ?>
                </small>
            <?php else : ?>
                <span class="com-users-method-title flex-grow-1 fs-5 fw-bold">
                    <?php echo $methodName ?>
                </span>
                <small class="com-users-method-name text-muted">
                    <?php echo $methodName ?>
                </small>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
