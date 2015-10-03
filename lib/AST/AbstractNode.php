<?php

namespace MacFJA\LexerExpression\AST;

/**
 * Class AbstractNode
 *
 * The base class for an AST node
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression\AST
 */
class AbstractNode
{
    /** @var array A doctrine Token */
    protected $operator;

    /**
     * Set the doctrine operator
     *
     * @param array $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }
    /**
     * List of operand, which can be instance of AbstractNode or any value
     *
     * @var AbstractNode[]|mixed[]
     */
    protected $operands = array();

    /**
     * Get all operands(attributes/parameters/values) of the node
     *
     * @return AbstractNode[]|mixed[]
     */
    public function getOperands()
    {
        return $this->operands;
    }

    /**
     * Add an operand in the node
     *
     * @param AbstractNode|mixed $operand
     */
    public function addOperand($operand)
    {
        $this->operands[] = $operand;
    }

    /** {@inheritdoc} */
    public function __toString()
    {
        $operands = array();
        foreach ($this->operands as $operand) {
            $operands[] = $operand.'';
        }
        return $this->operator['value'].'( '.implode(', ', $operands).' )';
    }

}