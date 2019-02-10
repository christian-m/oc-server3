<?php
/**
 * Verifies that class members are spaced correctly.
 *
 * PHP version 5
 *
 * @category  PHP
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found');
}

/**
 * Verifies that class members are spaced correctly.
 *
 * @category  PHP
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class OcCodeStyle_Sniffs_WhiteSpace_MemberVarSpacingSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $ignore   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $ignore[] = T_VAR;
        $ignore[] = T_WHITESPACE;

        $start = $stackPtr;
        $prev  = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if (isset(PHP_CodeSniffer_Tokens::$commentTokens[$tokens[$prev]['code']]) === true) {
            // Assume the comment belongs to the member var if it is on a line by itself.
            $prevContent = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($prev - 1), null, true);
            if ($tokens[$prevContent]['line'] !== $tokens[$prev]['line']) {
                // Check the spacing, but then skip it.
                $foundLines = ($tokens[$stackPtr]['line'] - $tokens[$prev]['line'] - 1);
                if ($foundLines > 0) {
                    $error = 'Expected 0 blank lines after member var comment; %s found';
                    $data  = array($foundLines);
                    $fix   = $phpcsFile->addFixableError($error, $prev, 'AfterComment', $data);
                    if ($fix === true) {
                        $phpcsFile->fixer->beginChangeset();
                        for ($i = ($prev + 1); $i <= $stackPtr; $i++) {
                            if ($tokens[$i]['line'] === $tokens[$stackPtr]['line']) {
                                break;
                            }

                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->addNewline($prev);
                        $phpcsFile->fixer->endChangeset();
                    }
                }//end if

                $start = $prev;
            }//end if
        }//end if

        // There needs to be 0 blank line before the var, if there are not comments, otherwise 1 blank line
        $expectedLines = 0;
        if ($start === $stackPtr) {
            // No comment found.
            $first = $phpcsFile->findFirstOnLine(PHP_CodeSniffer_Tokens::$emptyTokens, $start, true);
            if ($first === false) {
                $first = $start;
            }
        } elseif ($tokens[$start]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            $first = $tokens[$start]['comment_opener'];
            $openingBracket = $phpcsFile->findPrevious(T_OPEN_CURLY_BRACKET, ($first - 1), null);
            $isFirst = true;
            for ($i = $openingBracket + 1; $i <= $first - 1; $i++) {
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    $isFirst = false;
                    break;
                }
            }
            if (! $isFirst) {
                $expectedLines = 1;
            } else {
                $first--;
            }
        } else {
            $first = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($start - 1), null, true);
            $first = $phpcsFile->findNext(PHP_CodeSniffer_Tokens::$commentTokens, ($first + 1));
        }

        $prev       = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$emptyTokens, ($first - 1), null, true);
        $foundLines = ($tokens[$first]['line'] - $tokens[$prev]['line'] - 1);
        if ($foundLines === $expectedLines) {
            return;
        }

        if ($expectedLines === 0) {
            $error = 'Expected 0 blank lines before member var without comment or first member var; %s found';
        } else {
            $error = 'Expected 1 blank line before member var with comment; %s found';
        }
        $data  = array($foundLines);
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'Incorrect', $data);
        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();
            if ($expectedLines === 1) {
                for ($i = ($prev + 1); $i < $first; $i++) {
                    if ($tokens[$i]['line'] === $tokens[$prev]['line']) {
                        continue;
                    }

                    if ($tokens[$i]['line'] === $tokens[$first]['line']) {
                        $phpcsFile->fixer->addNewline(($i - 1));
                        break;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }
            } else {
                for ($i = ($prev + 1); $i < $first; $i++) {
                    if ($tokens[$i]['line'] === $tokens[$prev]['line']) {
                        continue;
                    }

                    $phpcsFile->fixer->replaceToken($i, '');
                }
            }

            $phpcsFile->fixer->endChangeset();
        }//end if
    }

    //end processMemberVar()

    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr): void
    {
        /*
            We don't care about normal variables.
        */
    }

    //end processVariable()

    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr): void
    {
        /*
            We don't care about normal variables.
        */
    }

    //end processVariableInString()
}//end class
