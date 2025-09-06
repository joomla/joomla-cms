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
?>
<div class="site-grid">
    <?php if ($doc->countModules('banner', true)) : ?>
        <div class="container-banner full-width">
            <jdoc:include type="modules" name="banner" style="none" />
        </div>
    <?php endif; ?>

    <?php if ($doc->countModules('top-a', true)) : ?>
        <div class="grid-child container-top-a">
            <jdoc:include type="modules" name="top-a" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($doc->countModules('top-b', true)) : ?>
        <div class="grid-child container-top-b">
            <jdoc:include type="modules" name="top-b" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($doc->countModules('sidebar-left', true)) : ?>
        <div class="grid-child container-sidebar-left">
            <jdoc:include type="modules" name="sidebar-left" style="card" />
        </div>
    <?php endif; ?>

    <div class="grid-child container-component">
        <jdoc:include type="modules" name="breadcrumbs" style="none" />
        <jdoc:include type="modules" name="main-top" style="card" />
        <jdoc:include type="message" />
        <main>
            <jdoc:include type="component" />
        </main>
        <jdoc:include type="modules" name="main-bottom" style="card" />
    </div>

    <?php if ($doc->countModules('sidebar-right', true)) : ?>
        <div class="grid-child container-sidebar-right">
            <jdoc:include type="modules" name="sidebar-right" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($doc->countModules('bottom-a', true)) : ?>
        <div class="grid-child container-bottom-a">
            <jdoc:include type="modules" name="bottom-a" style="card" />
        </div>
    <?php endif; ?>

    <?php if ($doc->countModules('bottom-b', true)) : ?>
        <div class="grid-child container-bottom-b">
            <jdoc:include type="modules" name="bottom-b" style="card" />
        </div>
    <?php endif; ?>
</div>

<?php if ($doc->countModules('footer', true)) : ?>
    <footer class="container-footer footer full-width">
        <div class="grid-child">
            <jdoc:include type="modules" name="footer" style="none" />
        </div>
    </footer>
<?php endif; ?>

<?php if ($doc->params->get('backTop') == 1) : ?>
    <a href="#top" id="back-top" class="back-to-top-link" aria-label="<?php echo Text::_('TPL_CASSIOPEIA_BACKTOTOP'); ?>">
        <span class="icon-arrow-up icon-fw" aria-hidden="true"></span>
    </a>
<?php endif; ?>

<jdoc:include type="modules" name="debug" style="none" />
