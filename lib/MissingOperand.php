<?php

namespace MacFJA\LexerExpression;

/**
 * Class MissingOperand
 *
 * Exception thrown when the number of found operand doesn't match with the arity of the operator.
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class MissingOperand extends \InvalidArgumentException
{

}