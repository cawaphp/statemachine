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

use Cawa\StateMachine\Transition;

class InvalidTransition extends StateMachineException
{
    /**
     * @param Transition $transition
     * @param \Exception $previous
     */
    public function __construct(Transition $transition, \Exception $previous = null)
    {
        parent::__construct(sprintf(
            "Invalid transition from '%s' to '%s'",
            $transition->getFrom()->getName(),
            $transition->getTo()
        ), 0, $previous);
    }
}
