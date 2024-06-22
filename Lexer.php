<?php

// Token types
const TOKEN_IDENTIFIER = 'IDENTIFIER';
const TOKEN_NUMBER = 'NUMBER';
const TOKEN_STRING = 'STRING';
const TOKEN_BRACE_OPEN = 'OPEN_BRACE';
const TOKEN_BRACE_CLOSE = 'CLOSE_BRACE';
const TOKEN_COLON = 'COLON';
const TOKEN_COMMA = 'COMMA';
const TOKEN_DOT = 'DOT';
const TOKEN_ASSIGNMENT = 'EQUAL';
const TOKEN_COMMENT = 'COMMENT';
const SEMI_COLON = "SEMI_COLON";
const TOKEN_ECHO = 'ECHO';
const TOKEN_PHP_TAG_OPEN = 'PHP_TAG_OPEN';
const TOKEN_PHP_TAG_CLOSE = 'PHP_TAG_CLOSE';
const TOKEN_NEWLINE = 'NEWLINE';
const EOF = "EOF";

// Tokenization function
function tokenize($code)
{
    $tokens = [];
    $state = 'START';
    $current_token = '';
    $position = 0;
    $length = strlen($code);
    $string_delimiter = '';

    while ($position < $length) {
        $char = $code[$position];

        switch ($state) {
            case 'START':
                if ($char === '<' && substr($code, $position, 5) === '<?php') {
                    $tokens[] = [TOKEN_PHP_TAG_OPEN, '<?php'];
                    $position += 4; // Move past '<?php'
                } elseif ($char === '?' && substr($code, $position, 2) === '?>') {
                    $tokens[] = [TOKEN_PHP_TAG_CLOSE, '?>'];
                    $position += 1; // Move past '? >'
                } elseif (ctype_alpha($char) || $char === '_') {
                    $state = 'IDENTIFIER';
                    $current_token .= $char;
                } elseif (ctype_digit($char)) {
                    $state = 'NUMBER';
                    $current_token .= $char;
                } elseif ($char === '"' || $char === "'") {
                    $state = 'STRING';
                    $current_token .= $char;
                    $string_delimiter = $char;
                } elseif ($char === '{') {
                    $tokens[] = [TOKEN_BRACE_OPEN, $char];
                } elseif ($char === '}') {
                    $tokens[] = [TOKEN_BRACE_CLOSE, $char];
                } elseif ($char === '.') {
                    $tokens[] = [TOKEN_DOT, $char];
                } elseif ($char === '=') {
                    $tokens[] = [TOKEN_ASSIGNMENT, $char];
                } elseif ($char === '#') {
                    $state = 'COMMENT';
                    $current_token = '#';
                } elseif ($char === ':') {
                    $tokens[] = [TOKEN_COLON, $char];
                } elseif ($char === ';') {
                    $tokens[] = [SEMI_COLON, $char];
                } elseif ($char === ',') {
                    $tokens[] = [TOKEN_COMMA, $char];
                } elseif ($char === "\n" || $char === "\r") {
                    $tokens[] = [TOKEN_NEWLINE, $char];
                } elseif (!ctype_space($char)) {
                    // Handle other characters or raise an error
                    // For now, let's skip any unsupported characters
                }
                break;

            case 'IDENTIFIER':
                if (ctype_alnum($char) || $char === '_') {
                    $current_token .= $char;
                } else {
                    if ($current_token === 'echo') {
                        $tokens[] = [TOKEN_ECHO, $current_token];
                    } else {
                        $tokens[] = [TOKEN_IDENTIFIER, $current_token];
                    }
                    $current_token = '';
                    $position--; // Backtrack to handle the current character
                    $state = 'START';
                }
                break;

            case 'NUMBER':
                if (ctype_digit($char)) {
                    $current_token += $char;
                } else {
                    $tokens[] = [TOKEN_NUMBER, $current_token];
                    $current_token = '';
                    $position--; // Backtrack to handle the current character
                    $state = 'START';
                }
                break;

            case 'STRING':
                $current_token .= $char;
                if ($char === $string_delimiter) {
                    $tokens[] = [TOKEN_STRING, $current_token];
                    $current_token = '';
                    $state = 'START';
                }
                break;

            case 'COMMENT':
                if ($char === "\n" || $char === "\r") {
                    $tokens[] = [TOKEN_COMMENT, $current_token];
                    $current_token = '';
                    $state = 'START';
                } else {
                    $current_token .= $char;
                }
                break;
        }

        $position++;
    }

    // Handle any remaining token
    if ($current_token !== '') {
        switch ($state) {
            case 'IDENTIFIER':
                $tokens[] = [TOKEN_IDENTIFIER, $current_token];
                break;
            case 'NUMBER':
                $tokens[] = [TOKEN_NUMBER, $current_token];
                break;
            case 'COMMENT':
                $tokens[] = [TOKEN_COMMENT, $current_token];
                break;
            case 'STRING':
                // Handle unterminated string
                $tokens[] = [TOKEN_STRING, $current_token];
                break;
        }
    }

    // Append EOF token
    $tokens[] = [EOF, null];

    return $tokens;
}

?>
