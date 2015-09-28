<?php

namespace MacFJA\LexerExpression;

/**
 * Class MissingParenthesisException
 *
 * Exception thrown when the Shunting Yard doesn't found the closing parenthesis of an opened group.
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class MissingParenthesisException extends \UnexpectedValueException
{

}