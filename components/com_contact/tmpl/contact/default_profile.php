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

?>
<?php if (PluginHelper::isEnabled('user', 'profile')) :
    $fields = $this->item->profile->getFieldset('profile'); ?>
    <div class="com-contact__profile contact-profile" id="users-profile-custom">
        <dl class="dl-horizontal">
            <?php foreach ($fields as $profile) :
                if ($profile->value) :
                    echo '<dt>' . $profile->label . '</dt>';
                    $profile->text = htmlspecialchars($profile->value, ENT_COMPAT, 'UTF-8');

                    switch ($profile->id) :
                        case 'profile_website':
                            $v_http = substr($profile->value, 0, 4);

                            if ($v_http === 'http') :
                                echo '<dd><a href="' . $profile->text . '">' . $this->escape(PunycodeHelper::urlToUTF8($profile->text)) . '</a></dd>';
                            else :
                                echo '<dd><a href="http://' . $profile->text . '">' . $this->escape(PunycodeHelper::urlToUTF8($profile->text)) . '</a></dd>';
                            endif;
                            break;

                        case 'profile_dob':
                            echo '<dd>' . HTMLHelper::_('date', $profile->text, Text::_('DATE_FORMAT_LC4'), false) . '</dd>';
                            break;

                        default:
                            echo '<dd>' . $profile->text . '</dd>';
                            break;
                    endswitch;
                endif;
            endforeach; ?>
        </dl>
    </div>
<?php endif; ?>
