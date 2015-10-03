<?php

require_once __DIR__.'/../vendor/autoload.php';

use MacFJA\LexerExpression\RPNOperator;
use MacFJA\LexerExpression\RPNSolver;
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

// -- RPNSolver

$solver = new RPNSolver();
$solver->addOperators(RPNOperator::buildListWithArity(array(
    $lexer::T_MULTIPLY,
    $lexer::T_DIVIDE,
    $lexer::T_PLUS,
    $lexer::T_MINUS
), 2));

$solver->setOperatorCallable(function($operator, array $args) {
    $value = null;
    if ($operator['value'] === '+') {
        $value = $args[0]['value'] + $args[1]['value'];
    } elseif ($operator['value'] === '-') {
        $value = $args[0]['value'] - $args[1]['value'];
    } elseif ($operator['value'] === '/') {
        if (0 == $args[1]['value']) {
            throw new \InvalidArgumentException('Can not divide by 0');
        }
        $value = $args[0]['value'] / $args[1]['value'];
    } elseif ($operator['value'] === '*') {
        $value = $args[0]['value'] * $args[1]['value'];
    }

    return array(
        'type' => MathLexer::T_VALUE,
        'value' => $value,
        'position' => -1
    );
});

$result = $solver->solve($rpnExpression);

echo 'Expected:   "0.17647058823529"' . PHP_EOL;
echo 'Calculated: "' . $result['value'] . '"' . PHP_EOL;