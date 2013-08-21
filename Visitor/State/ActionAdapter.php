<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

/**
 * ActionAdapter is the default behavior for all states
 */
abstract class ActionAdapter implements Action
{

    protected $currentNode = null;
    protected $context;

    public function __construct(Context $ctx)
    {
        $this->context = $ctx;
    }

    public function enterClass(\PHPParser_Node_Stmt_Class $cls)
    {
        throw new \LogicException('Cannot enter in class');
    }

    public function enterFile(\Trismegiste\Mondrian\Parser\PhpFile $fch)
    {
        throw new \LogicException('Cannot enter in file');
    }

    public function enterInterface(\PHPParser_Node_Stmt_Interface $cls)
    {
        throw new \LogicException('Cannot enter in interface');
    }

    public function enterMethod(\PHPParser_Node_Stmt_ClassMethod $meth)
    {
        throw new \LogicException('Cannot enter in method');
    }

    public function enterNamespace(\PHPParser_Node_Stmt_Namespace $node)
    {
        throw new \LogicException('Cannot enter in namespace');
    }

    public function leaveClass(\PHPParser_Node_Stmt_Class $cls)
    {
        
    }

    public function leaveFile(\Trismegiste\Mondrian\Parser\PhpFile $fch)
    {
        
    }

    public function leaveInterface(\PHPParser_Node_Stmt_Interface $cls)
    {
        
    }

    public function leaveMethod(\PHPParser_Node_Stmt_ClassMethod $meth)
    {
        
    }

    public function leaveNamespace(\PHPParser_Node_Stmt_Namespace $node)
    {
        
    }

}