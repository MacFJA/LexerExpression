<?php

namespace MacFJA\LexerExpression;

/**
 * Class RPNOperator
 *
 * Representation of a RPN operator.
 * Hold information about Token identifier (token type) and operator arity (number of argument/operand).
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class RPNOperator
{
    /** @var int The token type identifier */
    protected $type;
    /** @var int The arity of the operator (must be superior to 0) */
    protected $arity;

    /**
     * RPNOperator constructor.
     *
     * @param int $type
     * @param int $arity
     *
     * @throws InvalidArity
     */
    public function __construct($type, $arity)
    {
        $this->type = $type;
        $this->setArity($arity);
    }

    /**
     * @return int
     */
    public function getArity()
    {
        return $this->arity;
    }

    /**
     * @param int $arity
     *
     * @throws InvalidArity If the arity is not superior to 0
     */
    public function setArity($arity)
    {
        if (((int)$arity) < 1) {
            throw new InvalidArity();
        }

        $this->arity = (int) $arity;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Build a list of RPNOperator with the same arity
     *
     * @param int[] $tokensType List of token type
     * @param int   $arity      The arity of all token
     *
     * @return RPNOperator[]
     */
    public static function buildListWithArity($tokensType, $arity)
    {
        $list = array();
        foreach ($tokensType as $type) {
            $list[] = new static($type, $arity);
        }

        return $list;
    }
}