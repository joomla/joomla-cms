<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;

/** @var \Joomla\Component\Contact\Site\View\Contact\HtmlView $this */
$icon = $this->params->get('contact_icons') == 0;

/**
 * Marker_class: Class based on the selection of text, none, or icons
 * jicon-text, jicon-none, jicon-icon
 */
?>
<dl class="com-contact__address contact-address dl-horizontal">
    <?php
    if (
        ($this->params->get('address_check') > 0) &&
        ($this->item->address || $this->item->suburb  || $this->item->state || $this->item->country || $this->item->postcode)
    ) : ?>
        <dt>
            <?php if ($icon && !$this->params->get('marker_address')) : ?>
                <span class="icon-address" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_CONTACT_ADDRESS'); ?></span>
            <?php else : ?>
                <span class="<?php echo $this->params->get('marker_class'); ?>">
                    <?php echo $this->params->get('marker_address'); ?>
                </span>
            <?php endif; ?>
        </dt>

        <?php if ($this->item->address && $this->params->get('show_street_address')) : ?>
            <dd>
                <span class="contact-street">
                    <?php echo nl2br($this->item->address, false); ?>
                </span>
            </dd>
        <?php endif; ?>

        <?php if ($this->item->suburb && $this->params->get('show_suburb')) : ?>
            <dd>
                <span class="contact-suburb">
                    <?php echo $this->item->suburb; ?>
                </span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->state && $this->params->get('show_state')) : ?>
            <dd>
                <span class="contact-state">
                    <?php echo $this->item->state; ?>
                </span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->postcode && $this->params->get('show_postcode')) : ?>
            <dd>
                <span class="contact-postcode">
                    <?php echo $this->item->postcode; ?>
                </span>
            </dd>
        <?php endif; ?>
        <?php if ($this->item->country && $this->params->get('show_country')) : ?>
            <dd>
                <span class="contact-country">
                    <?php echo $this->item->country; ?>
                </span>
            </dd>
        <?php endif; ?>
    <?php endif; ?>

<?php if ($this->item->email_to && $this->params->get('show_email')) : ?>
    <dt>
        <?php if ($icon && !$this->params->get('marker_email')) : ?>
            <span class="icon-envelope" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_CONTACT_EMAIL_LABEL'); ?></span>
        <?php else : ?>
            <span class="<?php echo $this->params->get('marker_class'); ?>">
                <?php echo $this->params->get('marker_email'); ?>
            </span>
        <?php endif; ?>
    </dt>
    <dd>
        <span class="contact-emailto">
            <?php echo $this->item->email_to; ?>
        </span>
    </dd>
<?php endif; ?>

<?php if ($this->item->telephone && $this->params->get('show_telephone')) : ?>
    <dt>
        <?php if ($icon && !$this->params->get('marker_telephone')) : ?>
                <span class="icon-phone" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_CONTACT_TELEPHONE'); ?></span>
        <?php else : ?>
            <span class="<?php echo $this->params->get('marker_class'); ?>">
                <?php echo $this->params->get('marker_telephone'); ?>
            </span>
        <?php endif; ?>
    </dt>
    <dd>
        <span class="contact-telephone">
            <?php echo $this->item->telephone; ?>
        </span>
    </dd>
<?php endif; ?>
<?php if ($this->item->fax && $this->params->get('show_fax')) : ?>
    <dt>
        <?php if ($icon && !$this->params->get('marker_fax')) : ?>
            <span class="icon-fax" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_CONTACT_FAX'); ?></span>
        <?php else : ?>
            <span class="<?php echo $this->params->get('marker_class'); ?>">
                <?php echo $this->params->get('marker_fax'); ?>
            </span>
        <?php endif; ?>
    </dt>
    <dd>
        <span class="contact-fax">
        <?php echo $this->item->fax; ?>
        </span>
    </dd>
<?php endif; ?>
<?php if ($this->item->mobile && $this->params->get('show_mobile')) : ?>
    <dt>
        <?php if ($icon && !$this->params->get('marker_mobile')) : ?>
            <span class="icon-mobile" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_CONTACT_MOBILE'); ?></span>
        <?php else : ?>
            <span class="<?php echo $this->params->get('marker_class'); ?>">
                <?php echo $this->params->get('marker_mobile'); ?>
            </span>
        <?php endif; ?>
    </dt>
    <dd>
        <span class="contact-mobile">
            <?php echo $this->item->mobile; ?>
        </span>
    </dd>
<?php endif; ?>
<?php if ($this->item->webpage && $this->params->get('show_webpage')) : ?>
    <dt>
        <?php if ($icon && !$this->params->get('marker_webpage')) : ?>
            <span class="icon-home" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('COM_CONTACT_WEBPAGE'); ?></span>
        <?php else : ?>
            <span class="<?php echo $this->params->get('marker_class'); ?>">
                <?php echo $this->params->get('marker_webpage'); ?>
            </span>
        <?php endif; ?>
    </dt>
    <dd>
        <span class="contact-webpage">
            <a href="<?php echo $this->item->webpage; ?>" target="_blank" rel="noopener noreferrer">
            <?php echo $this->escape(PunycodeHelper::urlToUTF8($this->item->webpage)); ?></a>
        </span>
    </dd>
<?php endif; ?>
</dl>
