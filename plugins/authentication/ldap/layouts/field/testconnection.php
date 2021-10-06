<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

HTMLHelper::_('jquery.framework');

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $options         Options available for this field.
 */
?>
<script>
jQuery(function() {
	jQuery('#ldap-test1-btn').on('keypress click', function(e)
	{
		if (e.which === 13 || e.which === 32 || e.type === 'click')
		{
			jQuery('#ldap-test1-msg').empty().html('<joomla-alert type="info" close-text="<?php echo Text::_('JCLOSE'); ?>" dismiss="true"><div class="alert-wrapper"><div class="alert-message"><?php echo Text::_('PLG_LDAP_FIELD_TEST_STARTED'); ?></div></div></joomla-alert>');
			jQuery.ajax(
			{
				method: 'POST',
				url: 'index.php?option=com_ajax&plugin=ldap&group=authentication&format=json',
				data: jQuery('form[name="adminForm"]').serialize() +'&test=1&<?php echo Session::getFormToken(); ?>=1',
			})
			.done(function(data)
			{
				var ldap_response = jQuery.parseJSON(data);
				var ldpa_response_status = ldap_response.status == 1 ? 'success' : 'danger';
				jQuery('#ldap-test1-msg').empty().html('<joomla-alert type="'+ ldpa_response_status +'" close-text="<?php echo Text::_('JCLOSE'); ?>" dismiss="true"><div class="alert-heading"><span class="'+ ldpa_response_status +'"></span>'+ ldap_response.hdr +'</div><div class="alert-wrapper"><div class="alert-message">'+ ldap_response.msg +'</div></div></joomla-alert>');
			});
		}
	});
});
</script>
<button id="ldap-test1-btn" class="btn btn-secondary" type="button">
	<span class="icon-sync"></span> <?php echo Text::_('PLG_LDAP_FIELD_TEST_BTN'); ?>
</button>
<div id="ldap-test1-msg" style="margin: 10px 0 30px 0;" aria-live="polite"></div>