<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var array $displayData */

$clientId      = (string) ($displayData['clientId'] ?? '');
$refreshToken  = $displayData['refreshToken'] ?? false;
$tokenIssuedAt = (string) ($displayData['tokenIssuedAt'] ?? '');
$callbackUrl   = (string) ($displayData['callbackUrl'] ?? '');
$issueUrl      = (string) ($displayData['issueUrl'] ?? '');
$checkUrl      = (string) ($displayData['checkUrl'] ?? '');

if ($clientId === '') {
    echo '<div class="alert alert-warning">' . Text::_('COM_CONFIG_MAIL_OAUTH2_CLIENT_ID_REQUIRED') . '</div>';

    return;
}

?>
<div class="d-flex flex-column gap-2">
    <div class="btn-group" role="group">
        <a class="btn btn-primary" href="<?php echo Joomla\CMS\Router\Route::_($issueUrl); ?>">
            <?php echo Text::_('COM_CONFIG_MAIL_OAUTH2_BUTTON_' . ($refreshToken ? 'REISSUE' : 'ISSUE')); ?>
        </a>
        <a
            class="btn btn-outline-secondary<?php echo !$refreshToken ? ' disabled' : ''; ?>"
            href="<?php echo !$refreshToken ? '#' : Route::_($checkUrl); ?>"
            <?php echo !$refreshToken ? 'aria-disabled="true"' : ''; ?>
        >
            <?php echo Text::_('COM_CONFIG_MAIL_OAUTH2_BUTTON_CHECK'); ?>
        </a>
    </div>

    <?php if ($tokenIssuedAt !== '') : ?>
        <div class="small text-muted">
            <?php echo Text::sprintf('COM_CONFIG_MAIL_OAUTH2_TOKEN_ISSUED_AT', $tokenIssuedAt); ?>
        </div>
    <?php endif; ?>

    <div class="small text-muted">
        <?php echo Text::sprintf('COM_CONFIG_MAIL_OAUTH2_CALLBACK_HINT', $callbackUrl); ?>
    </div>
</div>
