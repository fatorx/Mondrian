<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor\State;

/**
 * Context is a contract for a parsing context
 */
interface Context
{

    public function setState(Action $state);
}