<?php declare(strict_types=1);

namespace Stratadox\GraphpFinder\Test;

use Fhaculty\Graph\Graph;
use PHPUnit\Framework\TestCase;
use Stratadox\GraphpFinder\AdaptedNetwork;
use Stratadox\Pathfinder\DynamicPathfinder;
use Stratadox\Pathfinder\NoPathAvailable;

/**
 * @testdox Adapting a Graphp network for use with the Pathfinder
 */
class Adapting_a_network extends TestCase
{
    /** @test */
    function finding_paths_through_a_simple_undirected_graph()
    {
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

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'B', 'A'], $shortestPath->between('D', 'A'));

        $shortestPathFromA = $shortestPath->from('A');

        $this->assertCount(3, $shortestPathFromA);
        $this->assertSame(['A', 'B'], $shortestPathFromA['B']);
        $this->assertSame(['A', 'C'], $shortestPathFromA['C']);
        $this->assertSame(['A', 'C', 'D'], $shortestPathFromA['D']);
    }

    /** @test */
    function finding_paths_through_a_simple_directed_graph()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $b = $graph->createVertex('B');
        $c = $graph->createVertex('C');
        $d = $graph->createVertex('D');

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);

        $shortestPath = DynamicPathfinder::operatingIn(AdaptedNetwork::from($graph));

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'C', 'A'], $shortestPath->between('D', 'A'));

        $shortestPathFromA = $shortestPath->from('A');

        $this->assertCount(3, $shortestPathFromA);
        $this->assertSame(['A', 'B'], $shortestPathFromA['B']);
        $this->assertSame(['A', 'C'], $shortestPathFromA['C']);
        $this->assertSame(['A', 'C', 'D'], $shortestPathFromA['D']);
    }

    /** @test */
    function finding_paths_through_a_directed_multi_graph()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $b = $graph->createVertex('B');
        $c = $graph->createVertex('C');
        $d = $graph->createVertex('D');

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $a->createEdge($c)->setWeight(15);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);

        $shortestPath = DynamicPathfinder::operatingIn(AdaptedNetwork::from($graph));

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'C', 'A'], $shortestPath->between('D', 'A'));

        $shortestPathFromA = $shortestPath->from('A');

        $this->assertCount(3, $shortestPathFromA);
        $this->assertSame(['A', 'B'], $shortestPathFromA['B']);
        $this->assertSame(['A', 'C'], $shortestPathFromA['C']);
        $this->assertSame(['A', 'C', 'D'], $shortestPathFromA['D']);
    }

    /** @test */
    function finding_paths_through_a_graph_with_negative_cycle()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $b = $graph->createVertex('B');
        $c = $graph->createVertex('C');
        $d = $graph->createVertex('D');

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);
        // This would keep Dijkstra's algorithm busy for eternity...
        $c->createEdge($c)->setWeight(-1);

        $shortestPath = DynamicPathfinder::operatingIn(AdaptedNetwork::from($graph));

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'C', 'A'], $shortestPath->between('D', 'A'));
    }

    /** @test */
    function cannot_find_a_path_if_the_start_does_not_exist()
    {
        $shortestPath = DynamicPathfinder::operatingIn(
            AdaptedNetwork::from(new Graph())
        );

        $this->expectException(NoPathAvailable::class);
        $shortestPath->from('Z');
    }
}
