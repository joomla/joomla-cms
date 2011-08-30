<?php
/**
 * Ensures that new classes are instantiated without brackets if they do not
 * have any parameters.
 *
 * @category  Classes
 * @package   Joomla.CodeSniffer
 * @author    Nikolai Plath
 * @version   CVS: $Id: InstantiateNewClassesSniff.php 508 2011-08-29 08:53:08Z elkuku $
 */

/**
 * Ensures that new classes are instantiated without brackets if they do not
 * have any parameters.
 *
 * @category  Classes
 * @package   Joomla.CodeSniffer
 */
class Joomla_Sniffs_Classes_InstantiateNewClassesSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Registers the token types that this sniff wishes to listen to.
     *
     * @return array
     */
    public function register()
    {
        return array(T_NEW);
    }//end register()

    /**
     * Process the tokens that this sniff is listening for.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $running = true;
        $valid = false;
        $started = false;

        $cnt = $stackPtr + 1;

        do
        {
            if( ! isset($tokens[$cnt]))
            {
                $running = false;
            }
            else
            {
                switch ($tokens[$cnt]['code'])
                {
                    case T_SEMICOLON:
                        $valid = true;
                        $running = false;
                        break;

                    case T_OPEN_PARENTHESIS :
                        $started = true;
                        break;

                    case T_VARIABLE :
                    case T_STRING :
                    case T_LNUMBER :
                    case T_CONSTANT_ENCAPSED_STRING :
                        if($started)
                        {
                            $valid = true;
                            $running = false;
                        }

                        break;

                    case T_CLOSE_PARENTHESIS :
                        if( ! $started)
                        {
                            $valid = true;
                        }

                         $running = false;
                        break;

                    case T_WHITESPACE :
                        break;
                }//switch

                $cnt ++;
            }
        }
        while ($running == true);

        if( ! $valid)
        {
            $error = 'Instanciating new classes without parameters does not require brackets.';
            $phpcsFile->addError($error, $stackPtr, 'New class');
        }
    }//function
}//class
