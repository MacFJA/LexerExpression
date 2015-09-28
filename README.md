# Lexer Expression

1. Description
1. What is Shunting Yard algorithm
1. The Shunting Yard implementation
1. The RPN expression Solver
1. Install
1. Usage
1. Similar projects
1. Documentations

## Description

The main goal of this library is to transform an expression into a computer understandable expression, by using

1. Doctrine Lexer
2. The Shunting Yard algorithm

As the input of the Shunting Yard is a Doctrine Lexer, the library is not limited to mathematics expression.

The library provide an "evaluator" of the Shunting Yard algorithm output.

## What is Shunting Yard algorithm

The best explanation can by found on [Wikipedia](https://en.wikipedia.org/wiki/Shunting-yard_algorithm), or on this [article](https://igor.io/2013/12/03/stack-machines-shunting-yard.html) of [@igorw](https://github.com/igorw).

But to make simple, the main idea is to transform a list of chars into multiple of small group.

Simple example: `(1 + 2) * 3` is really simple for us (human) to process, but a computer can do it, it need to split it into small chunk. And evaluate it piece by piece.
For a computer, the expression need to be evaluate like this:

```
        *
       / \
      +   3
     / \
    1   2
```
This a binary tree, and yes it's also a kind of AST.
So to solve the expression a computer first evaluate the group (we will name the result as **(a)**):

```
      +
     / \
    1   2
```
And then it evaluate the group:

```
   *
  / \
(a)  3
```

The Shunting Yard algorithm transform your expression into an intermediate expression: a RPN expression.
The RPN expression of `(1 + 2) * 3` is `1 2 + 3 *`.

This expression can be read as:
 - Parameter `1`
 - Parameter `2`
 - Operator `+` (that take 2 parameters: the two previous parameters)
 - Parameter `3`
 - Operator `*` (that take 2 parameters: the result of the operation and the previous parameter)

## The Shunting Yard implementation
 
Like most of Shunting Yard implementation, it can parse "simple" expression (with simple operator) and make parenthesis reduction.
It can parse unary, and binary functions (like `sin(x)`, `max(x,y)`) but also any functions (ex: `fct(a,b,c,x,y,z)`).

But the main "feature" it's the use of Doctrine Lexer.

## The RPN expression Solver

The RPN solver allow you to solve a RPN expression (a list of Doctrine Lexer token).
It support Operator/Function with any arity.

### Limitation

The Solver can not solver function with variable arity (or optional parameters)

## Install

To install with composer:

```
composer require macfja/lexer-expression
```

## Usage

### Very simple Doctrine DQL

```php
use \Doctrine\ORM\Query\Lexer;

$dql = 'SELECT u FROM User u WHERE u.id = MAX(5, 7)';
$lexer = new Lexer($dql);
$sy = new ShuntingYard();
$sy->setLexer($lexer);
$sy->setOpenParenthesis(Lexer::T_OPEN_PARENTHESIS);
$sy->setCloseParenthesis(Lexer::T_CLOSE_PARENTHESIS);
$sy->setOperators(array(
    new ShuntingYardOperator(Lexer::T_MAX,    10, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(Lexer::T_SELECT, 10, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(Lexer::T_FROM,   10, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(Lexer::T_WHERE,  2,  ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(Lexer::T_DOT,    10, ShuntingYardOperator::ASSOCIATIVITY_LEFT),
    new ShuntingYardOperator(Lexer::T_EQUALS, 10, ShuntingYardOperator::ASSOCIATIVITY_LEFT)
));
$sy->setArgumentSeparator(Lexer::T_COMMA);
var_dump($sy->parse());
```

### Very simple Mathematics expression

As simple mathematics expression, with a simple lexer

```php
class MathLexer extend \Doctrine\Common\Lexer
{
    const T_PLUS              = 1;
    const T_MINUS             = 2;
    const T_MULTIPLY          = 3;
    const T_DIVIDE            = 4;
    const T_CLOSE_PARENTHESIS = 5;
    const T_OPEN_PARENTHESIS  = 6;
    const T_VALUE             = 7;
    
    protected function getCatchablePatterns()
    {
        return array(
            '[0-9][\.0-9]*',
            '[\(\)\/+*-]'
        );
    }
    
    protected function getNonCatchablePatterns()
    {
        return array(
            '\s+'
        );
    }
    
    protected function getType(&$value)
    {
        switch ($value) {
            case '(':
                return self::T_OPEN_PARENTHESIS;
            case ')':
                return self::T_CLOSE_PARENTHESIS;
            case '+':
                return self::T_PLUS;
            case '-':
                return self::T_MINUS;
            case '/':
                return self::T_DIVIDE;
            case '*':
                return self::T_MULTIPLY;
            default:
                return self::T_VALUE;
        }
    }
}

// ----------

$expression = '(1 + 2) / ((3 + 4 * 5) - 6)'

$lexer = new MathLexer();
$lexer->setInput($expression);

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

// ----------

$solver = new RPNSolver();
$solver->addOperators(RPNOperator::buildListWithArity(array(
    $lexer::T_MULTIPLY,
    $lexer::T_DIVIDE,
    $lexer::T_PLUS,
    $lexer::T_MINUS
), 2));

$solver->setOperatorCallable(function($operator, array $args) {
    $a = 1;
    $b = 1;
    $c = 0;
    $d = 1;

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
        'type' => Lexer::T_VALUE,
        'value' => $value,
        'position' => -1
    );
});

var_dump($solver->solve($rpnExpression));
```

## Similar projects

 - [droptable/php-shunting-yard](https://github.com/droptable/php-shunting-yard)
 - [ircmaxell/php-math-parser](https://github.com/ircmaxell/php-math-parser)
 - [ngorchilov/psy](https://github.com/ngorchilov/psy)
 - [rbnvrw/PHPShuntingMathParser](https://github.com/rbnvrw/PHPShuntingMathParser)
 - [Isinlor/php-shunting-yard](https://github.com/Isinlor/php-shunting-yard)
 - [igorw/rpn](https://github.com/igorw/rpn)
 - [mephir/rpn](https://github.com/mephir/rpn)
 - [rn0/php-calc](https://github.com/rn0/php-calc)
 - [pear/Math_RPN](https://github.com/pear/Math_RPN)

## Documentations

 - http://rosettacode.org/wiki/Parsing/Shunting-yard_algorithm
 - https://en.wikipedia.org/wiki/Shunting-yard_algorithm
 - https://igor.io/2013/12/03/stack-machines-shunting-yard.html
 - https://www.klittlepage.com/2013/12/22/twelve-days-2013-shunting-yard-algorithm/
