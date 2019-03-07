<?php declare(strict_types=1);

namespace Stratadox\GraphpFinder\Test\Unit;

use Fhaculty\Graph\Graph;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Stratadox\GraphpFinder\AdaptedEnvironment;
use Stratadox\GraphpFinder\Test\Adapting_an_environment;

/**
 * This just checks some of the edge cases. For more interesting tests,
 * @see Adapting_an_environment
 * @testdox Checking the edge cases of the environment adapter
 */
class AdaptedEnvironmentTest extends TestCase
{
    /** @test */
    function non_existing_nodes_have_no_neighbours()
    {
        $environment = AdaptedEnvironment::from(new Graph());

        $this->assertEmpty($environment->neighboursOf('X'));
    }

    /** @test */
    function non_existing_nodes_are_not_neighbours()
    {
        $environment = AdaptedEnvironment::from(new Graph());

        $this->assertFalse($environment->areNeighbours('A', 'B'));
    }

    /** @test */
    function cannot_get_movement_cost_from_non_existing_node()
    {
        $environment = AdaptedEnvironment::from(new Graph());

        $this->expectException(InvalidArgumentException::class);

        $environment->movementCostBetween('A', 'B');
    }

    /** @test */
    function cannot_get_movement_cost_to_non_existing_node()
    {
        $graph = new Graph();
        $graph->createVertex('A');

        $environment = AdaptedEnvironment::from($graph);

        $this->expectException(InvalidArgumentException::class);

        $environment->movementCostBetween('A', 'B');
    }

    /** @test */
    function graphs_with_negatively_weighing_edges_have_negative_edge_costs()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $edge = $a->createEdge($a);
        $edge->setWeight(-1);

        $environment = AdaptedEnvironment::from($graph);

        $this->assertTrue($environment->hasNegativeEdgeCosts());
    }

    /** @test */
    function graphs_without_negatively_weighing_edges_have_no_negative_edge_costs()
    {
        $graph = new Graph();
        $a = $graph->createVertex('A');
        $edge = $a->createEdge($a);
        $edge->setWeight(1);

        $environment = AdaptedEnvironment::from($graph);

        $this->assertFalse($environment->hasNegativeEdgeCosts());
    }

    /** @test */
    function cannot_get_the_position_of_a_non_existing_node()
    {
        $environment = AdaptedEnvironment::from(new Graph());

        $this->expectException(InvalidArgumentException::class);

        $environment->positionOf('X');
    }
}
