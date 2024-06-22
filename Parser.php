<?php

require_once "./Lexer.php";

// AST Node Types
class Node {
    public $type;
    public $value;
    public $children;

    public function __construct($type, $value = null, $children = [])
    {
        $this->type = $type;
        $this->value = $value;
        $this->children = $children;
    }
}

// Parser class
class Parser {
    private $tokens;
    private $position;

    public function __construct($tokens)
    {
        $this->tokens = $tokens;
        $this->position = 0;
    }

    private function currentToken()
    {
        return $this->tokens[$this->position] ?? [EOF, null];
    }

    private function consumeToken()
    {
        return $this->tokens[$this->position++] ?? [EOF, null];
    }

    private function match($expectedType)
    {
        $current = $this->currentToken();
        if ($current[0] === $expectedType) {
            return $this->consumeToken();
        } else {
            throw new Exception("Expected token type: $expectedType, found: " . $current[0]);
        }
    }

    public function parse()
    {
        return $this->parseStatements();
    }

    private function parseStatements()
    {
        $statements = [];

        while ($this->currentToken()[0] !== EOF) {
            $statement = $this->parseStatement();
            if ($statement !== null) {
                $statements[] = $statement;
            }
        }

        return new Node('Program', null, $statements);
    }

    private function parseStatement()
    {
        switch ($this->currentToken()[0]) {
            case TOKEN_PHP_TAG_OPEN:
                $this->consumeToken(); // Skip PHP open tag
                return null;
            case TOKEN_PHP_TAG_CLOSE:
                $this->consumeToken(); // Skip PHP close tag
                return null;
            case TOKEN_IDENTIFIER:
                return $this->parseAssignment();
            case TOKEN_ECHO:
                return $this->parseEcho();
            case TOKEN_COMMENT:
                return $this->parseComment();
            case TOKEN_NEWLINE:
                $this->consumeToken(); // Skip newlines
                return null;
            default:
                throw new Exception("Unexpected token: " . $this->currentToken()[0]);
        }
    }

    private function parseAssignment()
    {
        $identifier = $this->match(TOKEN_IDENTIFIER);
        $this->match(TOKEN_ASSIGNMENT);
        $expression = $this->parseExpression();
        $this->match(SEMI_COLON);
        return new Node('Assignment', $identifier[1], [$expression]);
    }

    private function parseExpression()
    {
        switch ($this->currentToken()[0]) {
            case TOKEN_NUMBER:
                return new Node('NumberLiteral', $this->consumeToken()[1]);
            case TOKEN_STRING:
                return new Node('StringLiteral', $this->consumeToken()[1]);
            case TOKEN_IDENTIFIER:
                return new Node('Identifier', $this->consumeToken()[1]);
            case TOKEN_BRACE_OPEN:
                return $this->parseObject();
            default:
                throw new Exception("Unexpected token in expression: " . $this->currentToken()[0]);
        }
    }

    private function parseObject()
    {
        $this->match(TOKEN_BRACE_OPEN);
        $pairs = [];
        while ($this->currentToken()[0] !== TOKEN_BRACE_CLOSE) {
            $key = $this->match(TOKEN_IDENTIFIER);
            $this->match(TOKEN_COLON);
            $value = $this->parseExpression();
            $pairs[] = new Node('Pair', null, [new Node('Identifier', $key[1]), $value]);
            if ($this->currentToken()[0] === TOKEN_COMMA) {
                $this->consumeToken();
            }
        }
        $this->match(TOKEN_BRACE_CLOSE);
        return new Node('Object', null, $pairs);
    }

    private function parseEcho()
    {
        $this->match(TOKEN_ECHO);
        $expressions = [];
        while ($this->currentToken()[0] !== SEMI_COLON) {
            $expressions[] = $this->parseExpression();
            if ($this->currentToken()[0] === TOKEN_DOT) {
                $this->consumeToken(); // Ignore dots for concatenation in this example
            }
        }
        $this->match(SEMI_COLON);
        return new Node('Echo', null, $expressions);
    }

    private function parseComment()
    {
        $comment = $this->consumeToken();
        return new Node('Comment', $comment[1]);
    }
}

?>
