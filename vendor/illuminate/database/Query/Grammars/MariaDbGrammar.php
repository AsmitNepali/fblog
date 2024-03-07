<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinLateralClause;
use RuntimeException;

class MariaDbGrammar extends MySqlGrammar
{
    /**
     * Compile a "lateral join" clause.
     *
     *
     * @throws \RuntimeException
     */
    public function compileJoinLateral(JoinLateralClause $join, string $expression): string
    {
        throw new RuntimeException('This database engine does not support lateral joins.');
    }

    /**
     * Determine whether to use a legacy group limit clause for MySQL < 8.0.
     *
     * @return bool
     */
    public function useLegacyGroupLimit(Builder $query)
    {
        return false;
    }
}
