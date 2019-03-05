#GraphpFinder
An adapter to make [Graphp](https://github.com/graphp/graph) compatible with 
[Pathfinder](https://github.com/Stratadox/Pathfinder).

## Installation
Install with `composer require stratadox/graphp-finder`

## Features
This Graphp adapter aims to honour the [Graphp philosophy](https://github.com/graphp/graph#features)
in terms of supporting both directed and undirected graphs, as well as both 
[single- and multi graphs](https://en.wikipedia.org/wiki/Multigraph).

The above features roughly correspond to what Pathfinder calls a [Network](https://github.com/Stratadox/Pathfinder/blob/master/Graphs.md#networks).
In addition to networks, Pathfinder can operate on what it refers to as 
[Environments](https://github.com/Stratadox/Pathfinder/blob/master/Graphs.md#environments): 
graphs with Cartesian coordinates attached to their vertices.

An advantage of using an environment over a network is that search algorithms 
can be optimised through the use of [heuristics](https://en.wikipedia.org/wiki/Heuristic_(computer_science)), 
a technique used by Pathfinder's [A* implementation](https://github.com/Stratadox/Pathfinder#a).

This adapter can be used to transform a Graphp graph into either an environment 
or a network. In order to achieve compatibility, the environment adapter makes 
use of Graphp's concept of `attributes`.

Attributes in Graphp are key-value pairs; the environment adapter requires at 
least the keys `x` and `y` to be present, also using `z` when available.

## Examples
### Environment without GraphpFinder
The "normal" way to use the [Pathfinder](https://github.com/Stratadox/Pathfinder) 
module looks like this:

```php
<?php
use Stratadox\Pathfinder\DynamicPathfinder;
use Stratadox\Pathfinder\Graph\At;
use Stratadox\Pathfinder\Graph\Builder\GraphEnvironment;
use Stratadox\Pathfinder\Graph\Builder\WithEdge;

$environment = GraphEnvironment::create()
    ->withLocation('A', At::position(0, 0), WithEdge::to('B', 5)->andTo('C', 8))
    ->withLocation('B', At::position(0, 1), WithEdge::to('D', 9)->andTo('A', 1))
    ->withLocation('C', At::position(1, 0), WithEdge::to('D', 4)->andTo('A', 1))
    ->withLocation('D', At::position(1, 1), WithEdge::to('B', 3)->andTo('C', 9))
    ->make();

$shortestPath = DynamicPathfinder::operatingIn($environment);

assert(['A', 'C', 'D'] === $shortestPath->between('A', 'D'));
assert(['D', 'B', 'A'] === $shortestPath->between('D', 'A'));

assert([
    'B' => ['A', 'B'],
    'C' => ['A', 'C'],
    'D' => ['A', 'C', 'D'],
] === $shortestPath->from('A'));
```

### Network with GraphpFinder
In case you're using a [Graphp](https://github.com/graphp/graph) graph, you can 
use this adapter to make them compatible:

```php
<?php
use Fhaculty\Graph\Graph;
use Stratadox\GraphpFinder\AdaptedNetwork;
use Stratadox\Pathfinder\DynamicPathfinder;

// The same network as in the previous example, now represented as Graphp

$graph = new Graph();
$a = $graph->createVertex('A');
$b = $graph->createVertex('B');
$c = $graph->createVertex('C');
$d = $graph->createVertex('D');

$a->createEdgeTo($b)->setWeight(5);
$a->createEdgeTo($c)->setWeight(8);
$b->createEdgeTo($d)->setWeight(9);
$b->createEdgeTo($a)->setWeight(1);
$c->createEdgeTo($d)->setWeight(4);
$c->createEdgeTo($a)->setWeight(1);
$d->createEdgeTo($b)->setWeight(3);
$d->createEdgeTo($c)->setWeight(9);

$shortestPath = DynamicPathfinder::operatingIn(AdaptedNetwork::from($graph));

assert(['A', 'C', 'D'] === $shortestPath->between('A', 'D'));
assert(['D', 'B', 'A'] === $shortestPath->between('D', 'A'));

assert([
    'B' => ['A', 'B'],
    'C' => ['A', 'C'],
    'D' => ['A', 'C', 'D'],
] === $shortestPath->from('A'));
```

### Environment with GraphpFinder
You can use attributes to define the coordinates, improving the speed of finding 
the shortest path(s) through the graph:
```php
<?php
use Fhaculty\Graph\Graph;
use Stratadox\GraphpFinder\AdaptedEnvironment;
use Stratadox\Pathfinder\DynamicPathfinder;

// The same environment as in the previous example, now represented as Graphp

$graph = new Graph();
$a = $graph->createVertex('A');
$a->setAttribute('x', 0);
$a->setAttribute('y', 0);
$b = $graph->createVertex('B');
$b->setAttribute('x', 0);
$b->setAttribute('y', 1);
$c = $graph->createVertex('C');
$c->setAttribute('x', 1);
$c->setAttribute('y', 0);
$d = $graph->createVertex('D');
$d->setAttribute('x', 1);
$d->setAttribute('y', 1);

$a->createEdgeTo($b)->setWeight(5);
$a->createEdgeTo($c)->setWeight(8);
$b->createEdgeTo($d)->setWeight(9);
$b->createEdgeTo($a)->setWeight(1);
$c->createEdgeTo($d)->setWeight(4);
$c->createEdgeTo($a)->setWeight(1);
$d->createEdgeTo($b)->setWeight(3);
$d->createEdgeTo($c)->setWeight(9);


$shortestPath = DynamicPathfinder::operatingIn(AdaptedEnvironment::from($graph));

assert(['A', 'C', 'D'] === $shortestPath->between('A', 'D'));
assert(['D', 'B', 'A'] === $shortestPath->between('D', 'A'));

assert([
    'B' => ['A', 'B'],
    'C' => ['A', 'C'],
    'D' => ['A', 'C', 'D'],
] === $shortestPath->from('A'));
```

These latter examples look like it's more code, but that's only because creating 
a Graphp graph is somewhat more verbose compared to the [various graph 
builders](https://github.com/Stratadox/Pathfinder/blob/master/Graphs.md#creation)
that come with the Pathfinder module.

When you've already got a Graphp graph, using `AdaptedEnvironment::from($graph)` 
is a lot shorter, faster and less cumbersome than creating and maintaining an 
extra copy of your entire graph just for the pathfinder.


