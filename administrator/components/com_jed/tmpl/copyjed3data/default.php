<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

use Jed\Component\Jed\Administrator\Helper\JedmigrateHelper;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_jed.jedMigrate');
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <p>Clicking GO below will load a list of database commands which will extract JED 3 data into the new JED 4
                data structures.</p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row" id="fields-row">
        <div class="col-md-4" ><label class="form-label dbdefinitions">JED 3 Database
                Host:&nbsp;</label><?php echo $this->params->get('jed3_db_host'); ?></div>
        <div class="col-md-4"><label class="form-label dbdefinitions">JED 3 Database
                Name:&nbsp;</label><?php echo $this->params->get('jed3_db_database_name'); ?></div>
        <div class="col-md-4"><label class="form-label dbdefinitions">JED 3 Table
                Prefix:&nbsp;</label><?php echo $this->params->get('jed3_db_prefix'); ?></div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12"><a href="<?php echo $_SERVER['REQUEST_URI'] . '&task=GO'; ?>"
            <button class="btn btn-primary" type="button">Go</button>
            </a></div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            if ($this->task == "GO") {
                foreach ($this->migrate_xml->task as $task) {
                    echo '<div class="taskheading">' . $task->heading . '</div>';
                    foreach ($task->instruction as $instruction) {
                        echo '<div class="taskdescription">' . date("H:i:s") . " - " . $instruction->description . '</div>';
                        $instruction->sql = htmlspecialchars_decode($instruction->sql);
                        $sqlrows = explode(";", $instruction->sql);
                        foreach ($sqlrows as $sql) {
                          //  echo '<div class="taskdescription">'.date().' - Q - '.$sql . '<br/><br/></div>';
                            JedmigrateHelper::doSql($this->params, $sql);
                        }
                    }
                }
            }
            ?>

        </div>
    </div>
</div>





