<?php

namespace MacFJA\LexerExpression;

/**
 * Class MissingOperator
 *
 * Exception thrown when the RPN expression doesn't contains an operator.
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class MissingOperator extends \InvalidArgumentException
{

}