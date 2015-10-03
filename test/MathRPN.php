<?php

require_once __DIR__.'/../vendor/autoload.php';

use MacFJA\LexerExpression\ShuntingYard;
use MacFJA\LexerExpression\ShuntingYardOperator;

//------------------------------
//
// Classes definition
//
//------------------------------

include_once __DIR__.'/MathLexer.php';

//------------------------------
//
// Tests
//
//------------------------------

$expression = '(1 + 2) / ((3 + 4 * 5) - 6)';

$lexer = new MathLexer();
$lexer->setInput($expression);

// -- Shunting Yard

$shunting = new ShuntingYard();
$shunting->setLexer($lexer);
$shunting->setOpenParenthesis($lexer::T_OPEN_PARENTHESIS);
$shunting->setCloseParenthesis($lexer::T_CLOSE_PARENTHESIS);
$shunting->setOperators(array(
    new ShuntingYardOperator(MathLexer::T_MULTIPLY, 2, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(MathLexer::T_DIVIDE,   2, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(MathLexer::T_PLUS,     1, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(MathLexer::T_MINUS,    1, ShuntingYardOperator::ASSOCIATIVITY_LEFT)
));
$rpnExpression = $shunting->parse();

echo 'Expected:   "1 2 + 3 4 5 * + 6 - /"' . PHP_EOL;

$stringify = array();
foreach ($rpnExpression as $item) {
    $stringify[] = $item['value'];
}

echo 'Calculated: "' . implode(' ', $stringify) . '"' . PHP_EOL;