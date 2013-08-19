<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

use Trismegiste\Mondrian\Parser\PhpFile;

/**
 * Action is a contract for all transition of state whe parsing
 */
interface Action
{

    public function enterFile(PhpFile $fch);

    public function enterNamespace(\PHPParser_Node_Stmt_Namespace $node);

    public function enterClass(\PHPParser_Node_Stmt_Class $cls);

    public function enterInterface(\PHPParser_Node_Stmt_Interface $cls);

    public function enterMethod(\PHPParser_Node_Stmt_ClassMethod $meth);

    public function leaveFile(PhpFile $fch);

    public function leaveNamespace(\PHPParser_Node_Stmt_Namespace $node);

    public function leaveClass(\PHPParser_Node_Stmt_Class $cls);

    public function leaveInterface(\PHPParser_Node_Stmt_Interface $cls);

    public function leaveMethod(\PHPParser_Node_Stmt_ClassMethod $meth);
}