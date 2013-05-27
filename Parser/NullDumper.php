<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Parser;

/**
 * NullDumper is a dumper for dry run
 */
class NullDumper extends PhpPersistence
{

    /**
     * do nothing
     */
    public function write(PhpFile $aFile)
    {
        
    }

}
