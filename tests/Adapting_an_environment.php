<?php declare(strict_types=1);

namespace Stratadox\GraphpFinder\Test;

use Fhaculty\Graph\Graph;
use PHPUnit\Framework\TestCase;
use Stratadox\GraphpFinder\AdaptedEnvironment;
use Stratadox\Pathfinder\DynamicPathfinder;
use Stratadox\Pathfinder\FloydWarshallIndexer;
use Stratadox\Pathfinder\NoPathAvailable;

/**
 * @testdox Adapting a Graphp environment for use with the Pathfinder
 */
class Adapting_an_environment extends TestCase
{
    /** @test */
    function finding_paths_through_a_simple_undirected_2d_graph()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $a->setAttribute('x', 0);
        $a->setAttribute('y', 0);
        $b = $graph->createVertex('B');
        $b->setAttribute('x', 1);
        $b->setAttribute('y', 0);
        $c = $graph->createVertex('C');
        $c->setAttribute('x', 0);
        $c->setAttribute('y', 1);
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

        $shortestPath = DynamicPathfinder::operatingIn(
            AdaptedEnvironment::from($graph)
        );

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'B', 'A'], $shortestPath->between('D', 'A'));

        $shortestPathFromA = $shortestPath->from('A');

        $this->assertCount(3, $shortestPathFromA);
        $this->assertSame(['A', 'B'], $shortestPathFromA['B']);
        $this->assertSame(['A', 'C'], $shortestPathFromA['C']);
        $this->assertSame(['A', 'C', 'D'], $shortestPathFromA['D']);
    }

    /** @test */
    function finding_paths_through_a_simple_directed_2d_graph()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $a->setAttribute('x', 0);
        $a->setAttribute('y', 0);
        $b = $graph->createVertex('B');
        $b->setAttribute('x', 4);
        $b->setAttribute('y', 1);
        $c = $graph->createVertex('C');
        $c->setAttribute('x', 0);
        $c->setAttribute('y', 8);
        $d = $graph->createVertex('D');
        $d->setAttribute('x', 5);
        $d->setAttribute('y', 10);

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);

        $shortestPath = DynamicPathfinder::operatingIn(
            AdaptedEnvironment::from($graph)
        );

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'C', 'A'], $shortestPath->between('D', 'A'));

        $shortestPathFromA = $shortestPath->from('A');

        $this->assertCount(3, $shortestPathFromA);
        $this->assertSame(['A', 'B'], $shortestPathFromA['B']);
        $this->assertSame(['A', 'C'], $shortestPathFromA['C']);
        $this->assertSame(['A', 'C', 'D'], $shortestPathFromA['D']);
    }

    /** @test */
    function finding_paths_through_a_simple_directed_3d_graph()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $a->setAttribute('x', 0);
        $a->setAttribute('y', 0);
        $a->setAttribute('z', 0);
        $b = $graph->createVertex('B');
        $b->setAttribute('x', 4);
        $b->setAttribute('y', 1);
        $b->setAttribute('z', 0);
        $c = $graph->createVertex('C');
        $c->setAttribute('x', 0);
        $c->setAttribute('y', 8);
        $c->setAttribute('z', 2);
        $d = $graph->createVertex('D');
        $d->setAttribute('x', 5);
        $d->setAttribute('y', 10);
        $d->setAttribute('z', -1);

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);

        $environment = AdaptedEnvironment::from3D($graph);

        $this->assertEquals(0, $environment->positionOf('C')[0]);
        $this->assertEquals(8, $environment->positionOf('C')[1]);
        $this->assertEquals(2, $environment->positionOf('C')[2]);

        $this->assertEquals(5, $environment->positionOf('D')[0]);
        $this->assertEquals(10, $environment->positionOf('D')[1]);
        $this->assertEquals(-1, $environment->positionOf('D')[2]);
    }

    /** @test */
    function finding_paths_through_a_directed_2d_multi_graph()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $a->setAttribute('x', 0);
        $a->setAttribute('y', 0);
        $b = $graph->createVertex('B');
        $b->setAttribute('x', 4);
        $b->setAttribute('y', 1);
        $c = $graph->createVertex('C');
        $c->setAttribute('x', 0);
        $c->setAttribute('y', 8);
        $d = $graph->createVertex('D');
        $d->setAttribute('x', 5);
        $d->setAttribute('y', 10);

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $a->createEdge($c)->setWeight(15);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);

        $shortestPath = DynamicPathfinder::operatingIn(
            AdaptedEnvironment::from($graph)
        );

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
        $a->setAttribute('x', 0);
        $a->setAttribute('y', 0);
        $b = $graph->createVertex('B');
        $b->setAttribute('x', 4);
        $b->setAttribute('y', 1);
        $c = $graph->createVertex('C');
        $c->setAttribute('x', 0);
        $c->setAttribute('y', 8);
        $d = $graph->createVertex('D');
        $d->setAttribute('x', 5);
        $d->setAttribute('y', 10);

        $a->createEdge($b)->setWeight(5);
        $a->createEdge($c)->setWeight(8);
        $b->createEdge($d)->setWeight(9);
        $c->createEdge($d)->setWeight(4);

        $index = FloydWarshallIndexer::operatingIn(
            AdaptedEnvironment::from($graph)
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
            AdaptedEnvironment::from($graph)
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

        $shortestPath = DynamicPathfinder::operatingIn(
            AdaptedEnvironment::from($graph)
        );

        $this->assertSame(['A', 'C', 'D'], $shortestPath->between('A', 'D'));
        $this->assertSame(['D', 'C', 'A'], $shortestPath->between('D', 'A'));
    }

    /** @test */
    function cannot_find_a_path_if_the_start_does_not_exist()
    {
        $shortestPath = DynamicPathfinder::operatingIn(
            AdaptedEnvironment::from(new Graph())
        );

        $this->expectException(NoPathAvailable::class);
        $shortestPath->from('Z');
    }
}
