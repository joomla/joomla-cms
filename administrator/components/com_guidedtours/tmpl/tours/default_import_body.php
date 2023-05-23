<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>

<div class="container">
    <div class="row">
        <div class="control-group col-md-12 mt-3">
            <label for="importfile" class="form-label"><?php echo Text::sprintf('COM_GUIDEDTOURS_TOURS_IMPORT_FILE_LABEL'); ?></label>
            <div class="controls">
                <input type="file" id="importfile" name="importfile" accept=".json,application/json" class="form-control" />
            </div>
        </div>
    </div>
</div>
