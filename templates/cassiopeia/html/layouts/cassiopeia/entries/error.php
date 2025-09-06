<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

use Joomla\CMS\Language\Text;

extract($displayData);

$errorCode = $doc->error->getCode();
?>
<div class="site-grid">
    <div class="grid-child container-component">
        <?php if ($doc->countModules('error-' . $errorCode)) : ?>
            <div class="container">
                <jdoc:include type="message" />
                <main>
                    <jdoc:include type="modules" name="error-<?php echo $errorCode; ?>" style="none" />
                </main>
            </div>
        <?php else : ?>
            <h1 class="page-header"><?php echo Text::_('JERROR_LAYOUT_PAGE_NOT_FOUND'); ?></h1>
            <div class="card">
                <div class="card-body">
                    <jdoc:include type="message" />
                    <main>
                        <p><strong><?php echo Text::_('JERROR_LAYOUT_ERROR_HAS_OCCURRED_WHILE_PROCESSING_YOUR_REQUEST'); ?></strong></p>
                        <p><?php echo Text::_('JERROR_LAYOUT_NOT_ABLE_TO_VISIT'); ?></p>
                        <ul>
                            <li><?php echo Text::_('JERROR_LAYOUT_AN_OUT_OF_DATE_BOOKMARK_FAVOURITE'); ?></li>
                            <li><?php echo Text::_('JERROR_LAYOUT_MISTYPED_ADDRESS'); ?></li>
                            <li><?php echo Text::_('JERROR_LAYOUT_SEARCH_ENGINE_OUT_OF_DATE_LISTING'); ?></li>
                            <li><?php echo Text::_('JERROR_LAYOUT_YOU_HAVE_NO_ACCESS_TO_doc_PAGE'); ?></li>
                        </ul>
                        <p><?php echo Text::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?></p>
                        <p><a href="<?php echo $doc->baseurl; ?>/index.php" class="btn btn-secondary"><span class="icon-home" aria-hidden="true"></span> <?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a></p>
                        <hr>
                        <p><?php echo Text::_('JERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?></p>
                        <blockquote>
                            <span class="badge bg-secondary"><?php echo $doc->error->getCode(); ?></span> <?php echo htmlspecialchars($doc->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
                        </blockquote>
                    </main>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($doc->debug) : ?>
            <div>
                <?php echo $doc->renderBacktrace(); ?>
                <?php // Check if there are more Exceptions and render their data as well
                ?>
                <?php if ($doc->error->getPrevious()) : ?>
                    <?php $loop = true; ?>
                    <?php // Reference $doc->_error here and in the loop as setError() assigns errors to doc property and we need doc for the backtrace to work correctly
                    ?>
                    <?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions
                    ?>
                    <?php $doc->setError($doc->_error->getPrevious()); ?>
                    <?php while ($loop === true) : ?>
                        <p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
                        <p><?php echo htmlspecialchars($doc->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php echo $doc->renderBacktrace(); ?>
                        <?php $loop = $doc->setError($doc->_error->getPrevious()); ?>
                    <?php endwhile; ?>
                    <?php // Reset the main error object to the base error
                    ?>
                    <?php $doc->setError($doc->error); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if ($doc->countModules('footer')) : ?>
    <footer class="container-footer footer full-width">
        <div class="grid-child">
            <jdoc:include type="modules" name="footer" style="none" />
        </div>
    </footer>
<?php endif; ?>

<jdoc:include type="modules" name="debug" style="none" />
