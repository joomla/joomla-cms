<?php
/**
 * Squiz_Sniffs_Strings_ConcatenationSpacingSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: ConcatenationSpacingSniff.php 151 2010-11-26 01:07:46Z elkuku $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Makes sure there are is a spaces between the concatenation operator <b>.</b> and the strings being concatenated.
 *
 * Example:
 * <b class="bad">$a = $b.$c;</b>
 * <b class="good">$a = $b . $c;</b>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.0RC1
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Joomla_Sniffs_WhiteSpace_ConcatenationSpacingSniff implements PHP_CodeSniffer_Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return array(T_STRING_CONCAT);
	}//function

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer $stackPtr The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		if($tokens[($stackPtr + 1)]['code'] != T_WHITESPACE)
		{
			// space after
			$message = 'Concat operator must be followed by one space';
			$phpcsFile->addError($message, $stackPtr, 'Missing');
		}
		else
		{
			$found = strlen($tokens[($stackPtr + 1)]['content']);

			if($found > 1)
			{
				$error = sprintf('Expected 1 space after concat operator; %s found', $found);
				$phpcsFile->addError($error, $stackPtr, 'Too much');
			}
		}

		if($tokens[($stackPtr - 1)]['code'] != T_WHITESPACE)
		{
			// space before
			$message = 'Concat operator must be preceeded by one space';
			$phpcsFile->addError($message, $stackPtr, 'Missing');
		}
		else
		{
			if(strpos($tokens[($stackPtr - 2)]['content'], $phpcsFile->eolChar) !== false
			|| strpos($tokens[($stackPtr - 1)]['content'], $phpcsFile->eolChar) !== false)
			{
				// the dot is on a new line
				return;
			}

			$found = strlen($tokens[($stackPtr - 1)]['content']);

			if($found > 1)
			{
				$error = sprintf('Expected 1 space before concat operator; %s found', $found);
				$phpcsFile->addError($error, $stackPtr, 'Too much');
			}
		}
	}//function
}//class
