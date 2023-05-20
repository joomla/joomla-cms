<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_jed');
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_jed.reviewForm-showHideEntryForm')
    ->useScript('com_jed.reviewForm-changeRequired');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jed', JPATH_SITE);

$user    = JedHelper::getUser();
$canEdit = JedHelper::canUserEdit($this->item);

$isLoggedIn  = JedHelper::IsLoggedIn();
$redirectURL = JedHelper::getLoginlink();

echo LayoutHelper::render('review.guidelines', $this->extension_details);

?>
    <div id="reviewForm" style="display: none">

        <?php
        if (!$isLoggedIn) {
            try {
                $app = JFactory::getApplication();
            } catch (Exception $e) {
            }

            $app->enqueueMessage(Text::_('COM_JED_REVIEW_NO_ACCESS'), 'success');
            $app->redirect($redirectURL);
        } else {
            $default_values['extension_id'] = $this->extension_details->id;
            $default_values['flagged']      = 0;
            $default_values['published']    = 0;
            $default_values['ip_address']   = $_SERVER['REMOTE_ADDR'];
            $default_values['created_on']   = Factory::getDate()->toSql();
            $this->form->bind($default_values);
            $fieldsets['overview']['title']       = Text::_('COM_JED_REVIEW_OVERVIEW_TITLE') . $this->extension_details->title;
            $fieldsets['overview']['description'] = Text::_('COM_JED_REVIEW_OVERVIEW_DESCR');
            $fieldsets['overview']['fields']      = array('id',
                'supply_option_id', 'version',
                'extension_id', 'used_for');
            $fieldsets['overview']['hidden']      = array('id', 'extension_id');

            /* Display Radios of Supply Options */
            $optionstr = '';
            $default = 0;
            foreach ($this->supplytypes as $s) {
                $optionstr .= ' <option value="' . $s->supply_id . '">' . $s->supply_type . '</option> ';
                $default = (int)$s->supply_id;
            }
            try {
                $xml = new SimpleXMLElement('<field name="supply_option_id" type="radio"         label="COM_JED_REVIEWS_FIELD_SUPPLY_OPTION_ID_LABEL"           description="COM_JED_REVIEWS_FIELD_SUPPLY_OPTION_ID_DESCR"
               default="' . $default . '" class="btn-group">      ' . $optionstr . '  </field>');
            } catch (Exception $e) {
            }

            $field = $this->form->setField($xml);


            $fieldsets['details']['title']       = Text::_('COM_JED_REVIEW_DETAILS_TITLE');
            $fieldsets['details']['description'] = Text::_('COM_JED_REVIEW_DETAILS_DESCR');
            $fieldsets['details']['fields']      = array('title',
                'alias',
                'body');
            $fieldsets['details']['hidden']      = array('alias');

            $fieldsets['scores']['title']       = Text::_('COM_JED_REVIEW_SCORES_TITLE');
            $fieldsets['scores']['description'] = Text::_('COM_JED_REVIEW_SCORES_DESCR');
            $fieldsets['scores']['fields']      = array(
                'func_num',
                'ease_num',
                'support_num',
                'doc_num',
                'value_num',
                'functionality',
                'ease_of_use',
                'support',
                'documentation',
                'value_for_money',
                'overall_score');
            $fieldsets['scores']['hidden'] = array('overall_score',
                'func_num',
                'ease_num',
                'support_num',
                'doc_num',
                'value_num');




            $fieldsets['comments']['title']       = Text::_('COM_JED_REVIEW_COMMENTS_TITLE');
            $fieldsets['comments']['description'] = Text::_('COM_JED_REVIEW_COMMENTS_DESCR');
            $fieldsets['comments']['fields']      = array('functionality_comment',
                'ease_of_use_comment',
                'support_comment',
                'documentation_comment',
                'value_for_money_comment');
            $fieldsets['comments']['hidden']      = array();

            $fieldsets['hidden']['title']       = '';
            $fieldsets['hidden']['description'] = '';
            $fieldsets['hidden']['fields']      = array('flagged',
                'ip_address',
                'published',
                'created_on');
            $fieldsets['hidden']['hidden']      = $fieldsets['hidden']['fields']


            ?>

            <div class="review-edit front-end-edit">
                <?php if (!$canEdit) : ?>
                    <h3>
                        <?php throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403); ?>
                    </h3>
                <?php else : ?>
                    <form id="form-review"
                          action="<?php echo Route::_('index.php?option=com_jed&task=reviewform.save'); ?>"
                          method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

                        <?php
                        $fscount = 0;
                        foreach ($fieldsets as $fs) {
                            $fscount = $fscount + 1;
                            if ($fs['title'] <> '') {
                                if ($fscount > 1) {
                                    echo '</fieldset>';
                                }

                                echo '<fieldset class="reviewform"><legend>' . $fs['title'] . '</legend>';
                            }
                            if ($fs['description'] <> '') {
                                echo $fs['description'];
                            }
                            $fields       = $fs['fields'];
                            $hiddenFields = $fs['hidden'];
                            foreach ($fields as $field) {
                                if (in_array($field, $hiddenFields)) {
                                    $this->form->setFieldAttribute($field, 'type', 'hidden');
                                }

                                echo $this->form->renderField($field, null, null, array('class' => 'control-wrapper-' . $field));
                            }
                        }

                        ?>


                        <div class="control-group">
                            <div class="controls">

                                <?php if ($this->canSave) : ?>
                                    <button type="submit" class="validate btn btn-primary"
                                            onclick="mfTest()">
                                        <span class="fas fa-check" aria-hidden="true"></span>
                                        <?php echo Text::_('JSUBMIT'); ?>
                                    </button>
                                <?php endif; ?>
                                <a class="btn btn-danger"
                                   href="<?php echo Route::_('index.php?option=com_jed&task=reviewform.cancel'); ?>"
                                   title="<?php echo Text::_('JCANCEL'); ?>">
                                    <span class="fas fa-times" aria-hidden="true"></span>
                                    <?php echo Text::_('JCANCEL'); ?>
                                </a>
                              <?php /*  <button class="btn btn-info"
                                        onclick="mfTest()"
                                >
                                    <span class="fas fa-times" aria-hidden="true"></span>
                                    TEST
                                </button>
*/?>
                            </div>
                        </div>

                        <input type="hidden" name="option" value="com_jed"/>
                        <input type="hidden" name="task"
                               value="reviewform.save"/>
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </form>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
    </div>

<?php
echo LayoutHelper::render('review.report', $this->extension_details);

?>
