<?php

namespace MacFJA\LexerExpression\AST;

use MacFJA\LexerExpression\RPNOperator;
use MacFJA\LexerExpression\RPNSolver;

/**
 * Class TreeBuilder
 *
 * The worker class. Transform a RPN expression into an AST Tree
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression\AST
 */
class TreeBuilder
{
    /**
     * List of association between a RPNOperator and a Node class name
     *
     * @var array
     */
    protected $nodes = array();

    /**
     * Generate the AST tree
     *
     * @param array[] $rpn The RPN expression to transform
     *
     * @return AbstractNode
     */
    public function build($rpn)
    {
        $solver = new RPNSolver();
        foreach ($this->nodes as $node) {
            $solver->addOperator($node['operator']);
        }
        $that = $this;
        $solver->setOperatorCallable(function($operator, array $args) use ($that) {
            $node = $that->getNodeForTokenType($operator['type']);
            $node->setOperator($operator);
            foreach ($args as $argument) {
                if ($argument instanceof AbstractNode) {
                    $node->addOperand($argument);
                } else {
                    $node->addOperand($argument['value']);
                }
            }

            return $node;
        });

        return $solver->solve($rpn);
    }

    /**
     * Return a new AST node for the token type
     *
     * @param int $type The token type
     *
     * @return null|AbstractNode
     */
    protected function getNodeForTokenType($type)
    {
        foreach ($this->nodes as $node) {
            /** @var RPNOperator $operator */
            $operator = $node['operator'];
            if ($operator->getType() === $type) {
                return new $node['node']();
            }
        }
        return null;
    }

    /**
     * Add a operator/node association
     *
     * @param RPNOperator $operator      The RPNOperator
     * @param string      $nodeClassName The class name to associate
     */
    public function addNode($operator, $nodeClassName)
    {
        $this->nodes[] = array('operator' => $operator, 'node' => $nodeClassName);
    }
}