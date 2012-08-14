<?php

namespace Pipe\DirectiveProcessor;

/**
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
class Parser
{
    const T_ML_COMMENT_START = "/*";
    const T_ML_COMMENT = "*";
    const T_ML_COMMENT_END = "*/";
    const T_COMMENT = "//";
    const T_ALT_COMMENT = "#";
    const T_DIRECTIVE = '=';
    const T_CONTENT = 2;

    /**
     * Parses the Source Code
     *
     * @return array Stream of Tokens
     */
    function parse($source)
    {
        $source = (string) $source;

        if (empty($source)) {
            return array();
        }

        $header = true;
        $source = trim($source);
        $tokens = array();
        $lineNumber = 1;

        do {
            $pos  = strpos($source, "\n");
            $line = (false === $pos) ? substr($source, 0) : substr($source, 0, $pos);
            $line = trim($line);

            if ('' === $line) {
                // no op
            } else if (self::T_ML_COMMENT_START == substr($line, 0, 2)) {
                $tokens[] = array(self::T_ML_COMMENT_START, $line, $lineNumber);

            } else if (self::T_ML_COMMENT_END == substr($line, 0, 2)) {
                $tokens[] = array(self::T_ML_COMMENT_END, $line, $lineNumber);

            // T_COMMENT, T_ALT_COMMENT and T_ML_COMMENT can contain
            // directives so store them for later inspection
            } else if (self::T_COMMENT == substr($line, 0, 2)) {
                $comment = array(self::T_COMMENT, $line, $lineNumber);

            } else if (self::T_ALT_COMMENT == substr($line, 0, 1)) {
                $comment = array(self::T_ALT_COMMENT, $line, $lineNumber);

            } else if (self::T_ML_COMMENT == $line[0]) {
                $comment = array(self::T_ML_COMMENT, $line, $lineNumber);

            // Directives are only picked up before any code.
            // If anything other than comments and whitespace is coming
            // up, then we aren't in the header anymore.
            } else if ('' !== $line) {
                break;
            }

            // Look for directives in the body of comments 
            if (!empty($comment)) {
                list ($token, $content, $n) = $comment;
                $content = trim(substr($content, strlen($token)));

                if (!empty($content) and self::T_DIRECTIVE === $content[0]) {
                    $tokens[] = array(self::T_DIRECTIVE, trim(substr($content, 1)), $n);
                } else {
                    $tokens[] = $comment;
                }
                unset($comment);
            }

            // Break if no lines remain
            if (false === $pos) {
                $source = '';
                break;
            }

            // Break if there isn't any source left to process
            if (empty($source)) {
                break;
            }

            ++$lineNumber;
            $source = substr($source, $pos + 1);

        } while (true === $header);

        if (!empty($source)) {
            // Append the remaining Source Code as T_CONTENT
            $tokens[] = array(self::T_CONTENT, $source, $lineNumber);
        }
        return $tokens;
    }
}
