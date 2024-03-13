<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Templates\Administrator\View\Template\HtmlView $this */
?>
<div id="template-manager-delete" class="container-fluid">
    <div class="mt-2">
        <div class="col-md-12">
            <p><?php echo Text::sprintf('COM_TEMPLATES_MODAL_FILE_DELETE', str_replace('//', '/', $this->fileName)); ?></p>
        </div>
    </div>
</div>
