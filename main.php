<?php 


require_once "./Lexer.php";
require_once "./Parser.php";

// Example To use the Lexing to Generate the tokens
$code = file_get_contents("./Template.neza");
$tokens = tokenize($code);

// Parser to classify the tokens to Generate an AST...
$parser = new Parser($tokens);
$ast = $parser->parse();

// print_r($ast);


?>