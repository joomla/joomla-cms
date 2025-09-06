<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

extract($displayData);

?>
<header class="header container-header full-width<?php echo $entry !== 'error' && $doc->params->stickyHeader ? ' ' . $doc->params->stickyHeader : ''; ?>">

    <?php if ($entry !== 'error' && $doc->countModules('topbar')) : ?>
        <div class="container-topbar">
            <jdoc:include type="modules" name="topbar" style="none" />
        </div>
    <?php endif; ?>

    <?php if ($entry !== 'error' && $doc->countModules('below-top')) : ?>
        <div class="grid-child container-below-top">
            <jdoc:include type="modules" name="below-top" style="none" />
        </div>
    <?php endif; ?>

    <?php if ($doc->params->get('brand', 1)) : ?>
        <div class="grid-child">
            <div class="navbar-brand">
                <a class="brand-logo" href="<?php echo $doc->baseurl; ?>/">
                    <?php echo $doc->params->logo; ?>
                </a>
                <?php if ($doc->params->get('siteDescription')) : ?>
                    <div class="site-description"><?php echo htmlspecialchars($doc->params->get('siteDescription')); ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($doc->countModules('menu', true) || $doc->countModules('search', true)) : ?>
        <div class="grid-child container-nav">
            <?php if ($doc->countModules('menu', true)) : ?>
                <jdoc:include type="modules" name="menu" style="none" />
            <?php endif; ?>
            <?php if ($doc->countModules('search', true)) : ?>
                <div class="container-search">
                    <jdoc:include type="modules" name="search" style="none" />
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</header>
