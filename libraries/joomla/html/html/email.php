<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for cloaking email addresses
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JHtmlEmail
{
	/**
	 * Simple Javascript email Cloaker
	 *
	 * By default replaces an email with a mailto link with email cloaked
	 *
	 * @param   string   $mail    The -mail address to cloak.
	 * @param   boolean  $mailto  True if text and mailing address differ
	 * @param   string   $text    Text for the link
	 * @param   boolean  $email   True if text is an e-mail address
	 *
	 * @return  string  The cloaked email.
	 *
	 * @since   11.1
	 */
	public static function cloak($mail, $mailto = 1, $text = '', $email = 1)
	{
		/**
		 * Original approach breaks site content when combined with
		 * AJAX calls, i.e. JomSocial
		 *
		 * Current implementation relies on jQuery, please extend for
		 * other JS libraries
		 */
		// Convert text
		$mail = JHtmlEmail::_convertEncoding($mail);
		// Split email by @ symbol
		$mail = explode('@', $mail);
		$mail_parts = explode('.', $mail[1]);
		// Random number
		$__mail = '__' + rand(1, 100000);
		
		
		// Convert text
		$text = JHtmlEmail::_convertEncoding($text);
		// Split email by @ symbol
		$text = explode('@', $text);
		$text_parts = explode('.', $text[1]);
		// Random number
		$__text = '__' + rand(1, 100000);
		
		ob_start();
		?>
		<span id="span<?php echo $__mail ?>"><?php echo JText::_('JLIB_HTML_CLOAKING') ?></span>
		<script type="text/javascript">
		jQuery(document).load(function($){
		    $('#span<?php echo $__mail ?>').each(function(){
		        var $this = $(this),
		        prefix = '&#109;a' + 'i&#108;' + '&#116;o',
		        <?php echo $__mail ?> = "<?php echo @$mail[0] ?>" + "&#64;",
		        <?php echo $__text ?> = "<?php echo @$text[0] ?>" + "&#64;";
		        
		        <?php echo $__mail ?> += "<?php echo implode('" + "&#46;" + "',(array)$mail_parts) ?>";
		        <?php echo $__text ?> += "<?php echo implode('" + "&#46;" + "',(array)$text_parts) ?>";
		        
		        <?php if ($mailto) { ?>
		        $this.html('<a href="mailto:' + <?php echo $__mail ?> + '">' + <?php echo $__mail ?> + '</a>');
		            <?php if ($email) { ?>
		        $this.find('a').html(<?php echo $__text ?>);
		            <?php } ?>
		        <?php } else { ?>
		        $this.html(<?php echo $__mail ?>);
		        <?php } ?>
		    });
		});
		</script>
		<?php
		
		$replacement = ob_get_clean();
		return $replacement;
	}

	/**
	 * Convert encoded text
	 *
	 * @param   string  $text  Text to convert
	 *
	 * @return  string  The converted text.
	 *
	 * @since   11.1
	 */
	protected static function _convertEncoding($text)
	{
		// Replace vowels with character encoding
		$text = str_replace('a', '&#97;', $text);
		$text = str_replace('e', '&#101;', $text);
		$text = str_replace('i', '&#105;', $text);
		$text = str_replace('o', '&#111;', $text);
		$text = str_replace('u', '&#117;', $text);

		return $text;
	}
}
