<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

use Trismegiste\Mondrian\Parser\PhpFile;

/**
 * Package is the starting point for parsing a package
 *
 */
class Package extends ActionAdapter
{

    public function enterFile(PhpFile $fch)
    {
        $this->currentNode = $fch;
        $this->context->setState()
    }

    public function leaveFile(PhpFile $fch)
    {
        $this->currentNode = $fch;
    }

}