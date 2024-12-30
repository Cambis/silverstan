<?php

declare(strict_types=1);

namespace Cambis\Silverstan\FileCleaner;

use function in_array;
use function is_array;
use function php_strip_whitespace;
use function preg_match;
use function token_get_all;
use function trim;
use const T_CLASS;
use const T_ENUM;
use const T_INTERFACE;
use const T_STRING;
use const T_TRAIT;

/**
 * Modified from https://github.com/silverstripe/silverstripe-framework/blob/5/src/Core/Manifest/ClassContentRemover.php.
 */
final class FileCleaner
{
    /**
     * @param int $cutOffDepth The number of levels of curly braces to go before ignoring the content
     */
    public function cleanFile(string $filePath, int $cutOffDepth = 1): string
    {

        // Use PHP's built in method to strip comments and whitespace
        $contents = php_strip_whitespace($filePath);

        if (trim($contents) === '') {
            return $contents;
        }

        if (preg_match('/\b(?:class|interface|trait|enum)/i', $contents) === false) {
            return '';
        }

        // tokenize the file contents
        $tokens = token_get_all($contents);
        $cleanContents = '';
        $depth = 0;
        $startCounting = false;

        // iterate over all the tokens and only store the tokens that are outside $cutOffDepth
        foreach ($tokens as $token) {
            // only store the string literal of the token, that's all we need
            if (!is_array($token)) {
                $token = [
                    T_STRING,
                    $token,
                    null,
                ];
            }

            // only count if we see a class/interface/trait keyword
            $targetTokens = [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM];

            if (!$startCounting && in_array($token[0], $targetTokens, true)) {
                $startCounting = true;
            }

            // use curly braces as a sign of depth
            if ($token[1] === '{') {
                if ($depth < $cutOffDepth) {
                    $cleanContents .= $token[1];
                }

                if ($startCounting) {
                    ++$depth;
                }
            } elseif ($token[1] === '}') {
                if ($startCounting) {
                    --$depth;

                    // stop counting if we've just come out of the
                    // class/interface/trait declaration
                    if ($depth <= 0) {
                        $startCounting = false;
                    }
                }

                if ($depth < $cutOffDepth) {
                    $cleanContents .= $token[1];
                }
            } elseif ($depth < $cutOffDepth) {
                $cleanContents .= $token[1];
            }
        }

        // return cleaned class
        return trim($cleanContents);
    }
}
