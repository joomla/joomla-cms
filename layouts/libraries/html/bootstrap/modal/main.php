<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Utilities\ArrayHelper;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $selector  Unique DOM identifier for the modal. CSS id without #
 * @var   array   $params    Modal parameters. Default supported parameters:
 *                             - title        string   The modal title
 *                             - backdrop     mixed    A boolean select if a modal-backdrop element should be included (default = true)
 *                                                     The string 'static' includes a backdrop which doesn't close the modal on click.
 *                             - keyboard     boolean  Closes the modal when escape key is pressed (default = true)
 *                             - closeButton  boolean  Display modal close button (default = true)
 *                             - animation    boolean  Fade in from the top of the page (default = true)
 *                             - url          string   URL of a resource to be inserted as an <iframe> inside the modal body
 *                             - height       string   height of the <iframe> containing the remote resource
 *                             - width        string   width of the <iframe> containing the remote resource
 *                             - bodyHeight   int      Optional height of the modal body in viewport units (vh)
 *                             - modalWidth   int      Optional width of the modal in viewport units (vh)
 *                             - footer       string   Optional markup for the modal footer
 * @var   string  $body      Markup for the modal body. Appended after the <iframe> if the URL option is set
 */

$modalClasses = ['modal'];

if (!isset($params['animation']) || $params['animation']) {
    $modalClasses[] = 'fade';
}

$modalWidth       = isset($params['modalWidth']) ? round((int) $params['modalWidth'], -1) : '';
$modalDialogClass = '';

if ($modalWidth && $modalWidth > 0 && $modalWidth <= 100) {
    $modalDialogClass = ' jviewport-width' . $modalWidth;
}

$modalAttributes = [
    'tabindex' => '-1',
    'class'    => 'joomla-modal ' . implode(' ', $modalClasses)
];

if (isset($params['backdrop'])) {
    $modalAttributes['data-bs-backdrop'] = (is_bool($params['backdrop']) ? ($params['backdrop'] ? 'true' : 'false') : $params['backdrop']);
}

if (isset($params['keyboard'])) {
    $modalAttributes['data-bs-keyboard'] = (is_bool($params['keyboard']) ? ($params['keyboard'] ? 'true' : 'false') : 'true');
}

if (isset($params['url'])) {
    $url        = 'data-url="' . $params['url'] . '"';
    $iframeHtml = htmlspecialchars(LayoutHelper::render('libraries.html.bootstrap.modal.iframe', $displayData), ENT_COMPAT, 'UTF-8');
}
?>
<div id="<?php echo $selector; ?>" role="dialog" <?php echo ArrayHelper::toString($modalAttributes); ?> <?php echo $url ?? ''; ?> <?php echo isset($url) ? 'data-iframe="' . trim($iframeHtml) . '"' : ''; ?>>
    <div class="modal-dialog modal-lg<?php echo $modalDialogClass; ?>">
        <div class="modal-content">
            <?php
                // Header
            if (!isset($params['closeButton']) || isset($params['title']) || $params['closeButton']) {
                echo LayoutHelper::render('libraries.html.bootstrap.modal.header', $displayData);
            }

                // Body
                echo LayoutHelper::render('libraries.html.bootstrap.modal.body', $displayData);

                // Footer
            if (isset($params['footer'])) {
                echo LayoutHelper::render('libraries.html.bootstrap.modal.footer', $displayData);
            }
            ?>
        </div>
    </div>
</div>
