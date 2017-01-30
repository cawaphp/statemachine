# Сáша State Machine
-----

## Warning
Be aware that this package is still in heavy developpement.
Some breaking change will occure. Thank's for your comprehension.

## Features
* State > can have X transitions > can have X conditions
* `getPossibleTransitions()` Possible state depeding on current one
* `getGraph()` Graph of state machine
* `apply($state)` return true or throw a detailled exception 
* `apply($state, true)` return bool
* Events : 
  * Before state change
  * After state change 

## Basic Usage

```php
// Declare the state machine 
$stateMachine = new StateMachine("PENDING", new \stdClass());

$stateMachine->addState((new State("PENDING"))
    ->addTransition(new Transition("TOCONFIRM"))
    ->addTransition(new Transition("CONFIRMED"))
);

$stateMachine->addState((new State("TOCONFIRM"))
    ->addTransition(new Transition("CONFIRMED"))
    ->addTransition(new Transition("CANCELED"))
);

$returnTrue = new class extends Condition
{
    public function __invoke($subject) : bool
    {
        return true;
    }
};

$returnFalse = new class extends Condition
{
    public function __invoke($subject) : bool
    {
        return false;
    }
};

$stateMachine->addState((new State("READY"))
    ->addTransition((new Transition("CONFIRMED"))
        ->addCondition($returnFalse)
    )
    ->addTransition((new Transition("PROCESSED"))
        ->addCondition($returnTrue)
    )
    ->addTransition(new Transition("RETURN"))
);

$stateMachine
    ->addState(new State("PROCESSED"))
    ->addState(new State("CANCELED"))
    ->addState(new State("RETURN"));

// play with state

var_dump($stateMachine->getCurrentState()->getName());
// string(7) "PENDING"

$stateMachine->apply("TOCONFIRM");
var_dump($stateMachine->getCurrentState()->getName());
// string(9) "TOCONFIRM"

var_dump($stateMachine->apply("RETURN", true));
// bool(false)
var_dump($stateMachine->getLastException());
// object(Cawa\StateMachine\Exceptions\MissingTransition)#103 (7) {
//   ...
// }

var_dump($stateMachine->getPossibleTransitions());
// array(2) {
//   [0]=>
//   string(9) "CONFIRMED"
//   [1]=>
//   string(8) "CANCELED"
// }

var_dump($stateMachine->getGraph());
// string(369) "digraph G {
//   "PENDING" -> "TOCONFIRM"
//   "PENDING" -> "CONFIRMED"
//   "TOCONFIRM" -> "CONFIRMED"
//   "TOCONFIRM" -> "CANCELED"
//   "READY" -> "CONFIRMED" [label="if (class@anonymous/Test.php0x7fd8eb47c50a)"]
//   "READY" -> "PROCESSED" [label="if (class@anonymous/Test.php0x7fd8eb47c451)"]
//   "READY" -> "RETURN"
// }
// "
```

## Method getGraph()

This method return the raw GraphViz that you can use with official dot executable (`sudo apt-get install graphviz`) or with [this web interface](https://mdaines.github.io/viz.js/)

### License

Cawa is licensed under the GPL v3 License - see the `LICENSE` file for details
