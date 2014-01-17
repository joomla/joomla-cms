<?php
/**
 * Verifies that class members are spaced correctly.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: MemberVarSpacingSniff.php 8 2010-11-06 00:40:23Z elkuku $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if(class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false)
{
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found');
}

/**
 * Verifies that class members are spaced correctly.
 *
 * Example:
 * <b class="bad">Bad:</b>
 * class Foo
 * {
 *     <b class="bad">private $foo;</b>
 *     <b class="bad">private $bar;</b>
 * }
 *
 * <b class="good">Good:</b>
 * class Foo
 * {
 *     <b class="good">private $foo;</b>
 *     <b class="good">             </b>
 *     <b class="good">private $bar;</b>
 * }
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
class Joomla_Sniffs_WhiteSpace_MemberVarSpacingSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param integer                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // There needs to be 1 blank line before the var, not counting comments.
        $prevLineToken = null;

        for($i = ($stackPtr); $i > 0; $i--)
        {
            if(in_array($tokens[$i]['code'], PHP_CodeSniffer_Tokens::$commentTokens) === true)
            {
                // Skip comments.
                continue;
            }
            else if(strpos($tokens[$i]['content'], $phpcsFile->eolChar) === false)
            {
                // Not the end of the line.
                continue;
            }
            else
            {
                // If this is a WHITESPACE token, and the token right before
                // it is a DOC_COMMENT, then it is just the newline after the
                // member var's comment, and can be skipped.
                if($tokens[$i]['code'] === T_WHITESPACE
                && in_array($tokens[($i - 1)]['code'], PHP_CodeSniffer_Tokens::$commentTokens) === true)
                {
                    continue;
                }

                $prevLineToken = $i;
                break;
            }
        }//for

        if(is_null($prevLineToken) === true)
        {
            // Never found the previous line, which means
            // there are 0 blank lines before the member var.
            $foundLines = 0;
        }
        else
        {
            $prevContent = $phpcsFile->findPrevious(array(T_WHITESPACE, T_DOC_COMMENT), $prevLineToken, null, true);

            if($tokens[$prevContent]['code'] == T_OPEN_CURLY_BRACKET)
            {
                $foundLines = 1;
            }
            else
            {
                $foundLines  = ($tokens[$prevLineToken]['line'] - $tokens[$prevContent]['line']);
            }
        }//end if

        if($foundLines !== 1)
        {
            $error = sprintf('Expected 1 blank line before member var; %s found'
            , $foundLines);

            $phpcsFile->addError($error, $stackPtr, 'After');
        }
    }//function

    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param integer                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;
    }//function

    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param integer $stackPtr The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;
    }//function
}//class
