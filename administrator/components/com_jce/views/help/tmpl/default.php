<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('_JEXEC') or die('RESTRICTED');
?>
<div class="ui-jce">
    <div class="container-fluid">
        <nav>
          <div class="well sidebar-nav">
            <?php echo $this->model->renderTopics(); ?>
          </div>
        </nav>
        <main>
            <header class="navbar navbar-static-top">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-toggle="collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                </div>
            </header>
            <section>
                <iframe id="help-iframe" src="javascript:;" scrolling="auto" frameborder="0"></iframe>
            </section>
      </main>
    </div>
</div>