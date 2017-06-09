<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\StateMachine\Exceptions;

use Cawa\StateMachine\State;

class MissingTransition extends StateMachineException
{
    /**
     * @param State $state
     * @param string $stateName
     * @param \Exception $previous
     */
    public function __construct(State $state, string $stateName, \Exception $previous = null)
    {
        parent::__construct(sprintf(
            "Missing transition from '%s' to '%s'",
            $state->getName(),
            $stateName
        ), 0, $previous);
    }
}
