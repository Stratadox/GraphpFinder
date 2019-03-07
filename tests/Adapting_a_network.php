<?php declare(strict_types=1);

namespace Stratadox\GraphpFinder\Test;

use Fhaculty\Graph\Graph;
use PHPUnit\Framework\TestCase;
use Stratadox\GraphpFinder\AdaptedNetwork;
use Stratadox\Pathfinder\DynamicPathfinder;
use Stratadox\Pathfinder\FloydWarshallIndexer;
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
    function finding_paths_through_a_simple_undirected_graph_with_numeric_ids()
    {
        $graph = new Graph();
        $a = $graph->createVertex(1);
        $b = $graph->createVertex(2);
        $c = $graph->createVertex(3);
        $d = $graph->createVertex(4);

        $a->createEdgeTo($b)->setWeight(5);
        $a->createEdgeTo($c)->setWeight(8);
        $b->createEdgeTo($d)->setWeight(9);
        $b->createEdgeTo($a)->setWeight(1);
        $c->createEdgeTo($d)->setWeight(4);
        $c->createEdgeTo($a)->setWeight(1);
        $d->createEdgeTo($b)->setWeight(3);
        $d->createEdgeTo($c)->setWeight(9);

        $shortestPath = DynamicPathfinder::operatingIn(AdaptedNetwork::from($graph));

        $this->assertEquals([1, 3, 4], $shortestPath->between('1', '4'));
        $this->assertEquals([4, 2, 1], $shortestPath->between('4', '1'));

        $shortestPathFromA = $shortestPath->from('1');

        $this->assertCount(3, $shortestPathFromA);
        $this->assertEquals([1, 2], $shortestPathFromA[2]);
        $this->assertEquals([1, 3], $shortestPathFromA[3]);
        $this->assertEquals([1, 3, 4], $shortestPathFromA[4]);
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
    function building_an_index_of_the_graph()
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

        $index = FloydWarshallIndexer::operatingIn(
            AdaptedNetwork::from($graph)
        )->allShortestPaths();

        $this->assertEquals('B', $index->nextStepOnTheRoadBetween('A', 'B'));
        $this->assertEquals('C', $index->nextStepOnTheRoadBetween('A', 'D'));
    }

    /** @test */
    function building_an_informed_heuristic_from_indexed_graph()
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

        $heuristic = FloydWarshallIndexer::operatingIn(
            AdaptedNetwork::from($graph)
        )->heuristic();

        $this->assertEquals(5, $heuristic->estimate('A', 'B'));
        $this->assertEquals(12, $heuristic->estimate('A', 'D'));
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
