<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;

/** @var \Joomla\Component\Contact\Site\View\Contact\HtmlView $this */
?>
<?php if (PluginHelper::isEnabled('user', 'profile')) :
    $fields = $this->item->profile->getFieldset('profile'); ?>
    <div class="com-contact__profile contact-profile" id="users-profile-custom">
        <dl class="dl-horizontal">
            <?php foreach ($fields as $profile) :
                if ($profile->value) :
                    echo '<dt>' . $profile->label . '</dt>';
                    $profileText = htmlspecialchars($profile->value, ENT_COMPAT, 'UTF-8');

                    switch ($profile->id) :
                        case 'profile_website':
                            $v_http = substr($profile->value, 0, 4);

                            if ($v_http === 'http') :
                                echo '<dd><a href="' . $profileText . '">' . $this->escape(PunycodeHelper::urlToUTF8($profileText)) . '</a></dd>';
                            else :
                                echo '<dd><a href="http://' . $profileText . '">' . $this->escape(PunycodeHelper::urlToUTF8($profileText)) . '</a></dd>';
                            endif;
                            break;

                        case 'profile_dob':
                            echo '<dd>' . HTMLHelper::_('date', $profileText, Text::_('DATE_FORMAT_LC4'), false) . '</dd>';
                            break;

                        default:
                            echo '<dd>' . $profileText . '</dd>';
                            break;
                    endswitch;
                endif;
            endforeach; ?>
        </dl>
    </div>
<?php endif; ?>
