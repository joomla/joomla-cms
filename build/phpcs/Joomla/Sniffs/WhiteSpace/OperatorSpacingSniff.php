<?php
/**
 * Sniffs_Squiz_WhiteSpace_OperatorSpacingSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: OperatorSpacingSniff.php 8 2010-11-06 00:40:23Z elkuku $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Verifies that operators have valid spacing surrounding them.
 *
 * Example:
 * <b class="bad">$a=$b+$c;</b>
 * <b class="good">$a = $b + $c;</b>
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
class Joomla_Sniffs_WhiteSpace_OperatorSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
    );

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        $comparison = PHP_CodeSniffer_Tokens::$comparisonTokens;
        $operators  = PHP_CodeSniffer_Tokens::$operators;
        $assignment = PHP_CodeSniffer_Tokens::$assignmentTokens;

        return array_unique(array_merge($comparison, $operators, $assignment));
    }//function

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The current file being checked.
     * @param integer              $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Skip default values in function declarations.
        if($tokens[$stackPtr]['code'] === T_EQUAL
            || $tokens[$stackPtr]['code'] === T_MINUS
        )
        {
            if(isset($tokens[$stackPtr]['nested_parenthesis']) === true)
            {
                $parenthesis = array_keys($tokens[$stackPtr]['nested_parenthesis']);
                $bracket     = array_pop($parenthesis);
                if(isset($tokens[$bracket]['parenthesis_owner']) === true)
                {
                    $function = $tokens[$bracket]['parenthesis_owner'];
                    if($tokens[$function]['code'] === T_FUNCTION
                        || $tokens[$function]['code'] === T_CLOSURE
                    )
                    {
                        return;
                    }
                }
            }
        }

        if($tokens[$stackPtr]['code'] === T_EQUAL)
        {
            // Skip for '=&' case.
            if(isset($tokens[($stackPtr + 1)]) === true && $tokens[($stackPtr + 1)]['code'] === T_BITWISE_AND)
            {
                return;
            }
        }

        if($tokens[$stackPtr]['code'] === T_BITWISE_AND)
        {
            // If it's not a reference, then we expect one space either side of the
            // bitwise operator.
            if($phpcsFile->isReference($stackPtr) === true)
            {
                return;
            }

            // Check there is one space before the & operator.
            if($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE)
            {
                $error = 'Expected 1 space before "&" operator; 0 found';
                $phpcsFile->addError($error, $stackPtr, 'NoSpaceBeforeAmp');
            }
            else
            {
                if(strlen($tokens[($stackPtr - 1)]['content']) !== 1)
                {
                    $error = 'Expected 1 space before "&" operator; %s found';
                    $phpcsFile->addError($error, $stackPtr, 'SpacingBeforeAmp');
                }
            }

            // Check there is one space after the & operator.
            if($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE)
            {
                $error = 'Expected 1 space after "&" operator; 0 found';
                $phpcsFile->addError($error, $stackPtr, 'NoSpaceAfterAmp');
            }
            else
            {
                if(strlen($tokens[($stackPtr + 1)]['content']) !== 1)
                {
                    $error = 'Expected 1 space after "&" operator; %s found';
                    $phpcsFile->addError($error, $stackPtr, 'SpacingAfterAmp');
                }
            }

            return;
        }//end if

        if($tokens[$stackPtr]['code'] === T_MINUS)
        {
            // Check minus spacing, but make sure we aren't just assigning
            // a minus value or returning one.
            $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            if($tokens[$prev]['code'] === T_RETURN)
            {
                // Just returning a negative value; eg. (return -1).
                return;
            }

            if(in_array($tokens[$prev]['code'], PHP_CodeSniffer_Tokens::$operators) === true)
            {
                // Just trying to operate on a negative value; eg. ($var * -1).
                return;
            }

            if(in_array($tokens[$prev]['code'], PHP_CodeSniffer_Tokens::$comparisonTokens) === true)
            {
                // Just trying to compare a negative value; eg. ($var === -1).
                return;
            }

            if(in_array($tokens[$prev]['code'], PHP_CodeSniffer_Tokens::$assignmentTokens) === true)
            {
                // Just trying to assign a negative value; eg. ($var = -1).
                return;
            }

            // A list of tokens that indicate that the token is not
            // part of an arithmetic operation.
            $invalidTokens = array(
                              T_COMMA,
                              T_OPEN_PARENTHESIS,
                              T_OPEN_SQUARE_BRACKET,
                              T_DOUBLE_ARROW,
                              T_COLON,
                              T_INLINE_THEN,
                              T_INLINE_ELSE,
                              T_CASE,
                             );

            if(in_array($tokens[$prev]['code'], $invalidTokens) === true)
            {
                // Just trying to use a negative value; eg. myFunction($var, -2).
                return;
            }
        }//end if

        $operator = $tokens[$stackPtr]['content'];

        if($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE)
        {
            $error = "Expected 1 space before \"$operator\"; 0 found";
            $phpcsFile->addError($error, $stackPtr, 'NoSpaceBefore');
        }
        else if (strlen($tokens[($stackPtr - 1)]['content']) !== 1)
        {
            // Don't throw an error for assignments, because other standards allow
            // multiple spaces there to align multiple assignments.
            if (in_array($tokens[$stackPtr]['code'], PHP_CodeSniffer_Tokens::$assignmentTokens) === false) {
                $error = 'Expected 1 space before "%s"; %s found';
                $phpcsFile->addError($error, $stackPtr, 'SpacingBefore');
            }
        }

        if($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE)
        {
	        // Error downgraded to Warning due to code needing review
            $error = "Expected 1 space after \"$operator\"; 0 found";
            $phpcsFile->addWarning($error, $stackPtr, 'NoSpaceAfter');
        }
        else if (strlen($tokens[($stackPtr + 1)]['content']) !== 1)
        {
            $error = 'Expected 1 space after "%s"; %s found';
            $phpcsFile->addError($error, $stackPtr, 'SpacingAfter');
        }

    }//end process()


}//end class

?>
