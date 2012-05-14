<?php

namespace Pipe\Util;

class Shellwords
{
    static function split($line)
    {
        $line .= ' ';

        $pattern = '/\G\s*(?>([^\s\\\'\"]+)|\'([^\']*)\'|"((?:[^\"\\\\]|\\.)*)"|(\\.?)|(\S))(\s|\z)?/m';
        preg_match_all($pattern, $line, $matches, PREG_SET_ORDER);

        $words = array();
        $field = '';

        foreach ($matches as $set) {
            # Index #0 is the full match.
            array_shift($set);

            @list($word, $sq, $dq, $esc, $garbage, $sep) = $set;

            if ($garbage) {
                throw new \UnexpectedValueException("Unmatched double quote: '$line'");
            }

            $field .= ($dq ?: $sq ?: $word);

            if (strlen($sep) > 0) {
                $words[] = $field;
                $field = '';
            }
        }

        return $words;
    }

    static function join($pieces)
    {
        return join(' ', array_map("escapeshellarg", $pieces));
    }
}
