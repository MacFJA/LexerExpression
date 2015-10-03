<?php

require_once __DIR__.'/../vendor/autoload.php';

use MacFJA\LexerExpression\AST\AbstractNode;
use MacFJA\LexerExpression\AST\TreeBuilder;
use MacFJA\LexerExpression\RPNOperator;
use MacFJA\LexerExpression\ShuntingYard;
use MacFJA\LexerExpression\ShuntingYardOperator;

//------------------------------
//
// Classes definition
//
//------------------------------

include_once __DIR__.'/MathLexer.php';

class PlusNode extends AbstractNode {}
class MinusNode extends AbstractNode {}
class MultiplyNode extends AbstractNode {}
class DivideNode extends AbstractNode {}

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

// -- AST Tree Builder

$ast = new TreeBuilder();
$ast->addNode(new RPNOperator($lexer::T_MULTIPLY, 2), 'MultiplyNode');
$ast->addNode(new RPNOperator($lexer::T_DIVIDE, 2), 'DivideNode');
$ast->addNode(new RPNOperator($lexer::T_PLUS, 2), 'PlusNode');
$ast->addNode(new RPNOperator($lexer::T_MINUS, 2), 'MinusNode');

$result = $ast->build($rpnExpression);

echo 'Expected:   "/( +( 1, 2 ), -( +( 3, *( 4, 5 ) ), 6 ) )"' . PHP_EOL;
echo 'Calculated: "' . $result . '"' . PHP_EOL;