<?php declare(strict_types=1);

namespace Stratadox\GraphpFinder;

use function array_key_exists;
use Fhaculty\Graph\Edge\Base as Edge;
use Fhaculty\Graph\Exception\OutOfBoundsException;
use Fhaculty\Graph\Graph;
use InvalidArgumentException;
use Stratadox\Pathfinder\Environment;
use Stratadox\Pathfinder\Graph\At;
use Stratadox\Pathfinder\Graph\Ids;
use Stratadox\Pathfinder\Labels;
use Stratadox\Pathfinder\Position;

final class AdaptedEnvironment implements Environment
{
    private $graph;
    private $coordinate;
    private $position = [];

    private function __construct(Graph $graph, array $coordinateOffset)
    {
        $this->graph = $graph;
        $this->coordinate = $coordinateOffset;
    }

    public static function from(Graph $graph): Environment
    {
        return new self($graph, ['x', 'y']);
    }

    public static function from3D(Graph $graph): Environment
    {
        return new self($graph, ['x', 'y', 'z']);
    }

    public function all(): Labels
    {
        return Ids::consistingOf(...$this->graph->getVertices()->getIds());
    }

    public function neighboursOf(string $node): Labels
    {
        $neighbours = [];
        try {
            $start = $this->graph->getVertices()->getVertexId($node);
            /** @var Edge $edge */
            foreach ($start->getEdgesOut() as $edge) {
                $neighbours[] = (string) $edge->getVertexToFrom($start)->getId();
            }
        } catch (OutOfBoundsException $e) {
            // no node, no neighbours
        }
        return Ids::consistingOf(...$neighbours);
    }

    public function areNeighbours(string $source, string $neighbour): bool
    {
        try {
            $start = $this->graph->getVertices()->getVertexId($source);
        } catch (OutOfBoundsException $e) {
            // no source node, no neighbours
            return false;
        }
        /** @var Edge $edge */
        foreach ($start->getEdgesOut() as $edge) {
            if ((string) $edge->getVertexToFrom($start)->getId() === $neighbour) {
                return true;
            }
        }
        return false;
    }

    public function has(string $node): bool
    {
        return $this->graph->hasVertex($node);
    }

    public function movementCostBetween(
        string $source,
        string $neighbour
    ): float {
        try {
            $start = $this->graph->getVertices()->getVertexId($source);
        } catch (OutOfBoundsException $e) {
            throw new InvalidArgumentException("Vertex $source not found.");
        }
        /** @var Edge $edge */
        foreach ($start->getEdgesOut() as $edge) {
            if ((string) $edge->getVertexToFrom($start)->getId() === $neighbour) {
                return (float) $edge->getWeight();
            }
        }
        throw new InvalidArgumentException(
            "Vertices $source and $neighbour are not neighbours."
        );
    }

    public function hasNegativeEdgeCosts(): bool
    {
        /** @var Edge $edge */
        foreach ($this->graph->getEdges() as $edge) {
            if ($edge->getWeight() < 0) {
                return true;
            }
        }
        return false;
    }

    public function positionOf(string $node): Position
    {
        if (!array_key_exists($node, $this->position)) {
            try {
                $vertex = $this->graph->getVertices()->getVertexId($node);
            } catch (OutOfBoundsException $e) {
                throw new InvalidArgumentException($e->getMessage(), 0, $e);
            }
            $coordinates = [];
            foreach ($this->coordinate as $key) {
                $coordinates[] = $vertex->getAttribute($key, 0);
            }
            $this->position[$node] = At::position(...$coordinates);
        }
        return $this->position[$node];
    }
}
