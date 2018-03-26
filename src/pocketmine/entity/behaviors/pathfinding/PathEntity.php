<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\behaviors\pathfinding;

use pocketmine\entity\Mob;
use pocketmine\math\Vector3;

class PathEntity{

    /** @var PathPoint[] The actual points in the path */
    private $points;

    /** @var int PathEntity Array Index the Entity is currently targeting */
    private $currentPathIndex;

    /** @var int The total length of the path */
    private $pathLength;

    public function __construct(array $pathPoints){
        $this->points = $pathPoints;
        $this->pathLength = count($pathPoints);
    }

    /**
     * Directs this path to the next point in its array
     */
    public function incrementPathIndex() : void{
        ++$this->currentPathIndex;
    }

    /**
     * Returns true if this path has reached the end
     *
     * @return bool
     */
    public function isFinished() : bool{
        return $this->currentPathIndex >= $this->pathLength;
    }

    /**
     * Returns the last PathPoint of the Array
     *
     * @return null|PathPoint
     */
    public function getFinalPathPoint() : ?PathPoint{
        return $this->pathLength > 0 ? $this->points[$this->pathLength - 1] : null;
    }

    /**
     * Return the PathPoint located at the specified PathIndex, usually the current one
     *
     * @param int $index
     * @return PathPoint
     */
    public function getPathPointFromIndex(int $index) : PathPoint{
        return $this->points[$index];
    }

    public function getCurrentPathLength() : int{
        return $this->pathLength;
    }

    public function setCurrentPathLength(int $pathLength) : void{
        $this->pathLength = $pathLength;
    }

    public function getCurrentPathIndex() : int{
        return $this->currentPathIndex;
    }

    public function setCurrentPathIndex(int $currentPathIndex) : void{
        $this->currentPathIndex = $currentPathIndex;
    }

    /**
     * Gets the vector of the PathPoint associated with the given index.
     *
     * @param Mob $mob
     * @param int $index
     * @return Vector3
     */
    public function getVectorFromIndex(Mob $mob, int $index) : Vector3{
        $x = $this->points[$index]->xCoord + ((int)($mob->width + 1.0)) * 0.5;
        $y = $this->points[$index]->yCoord;
        $z = $this->points[$index]->zCoord + ((int)($mob->width + 1.0)) * 0.5;

        return new Vector3($x, $y, $z);
    }

    /**
     * Returns the current PathEntity target node as Vector3
     *
     * @param Mob $mob
     * @return Vector3
     */
    public function getPosition(Mob $mob) : Vector3{
        return $this->getVectorFromIndex($mob, $this->currentPathIndex);
    }

    /**
     * Returns true if the EntityPath are the same. Non instance related equals.
     *
     * @param null|PathEntity $pathEntity
     * @return bool
     */
    public function isSamePath(?PathEntity $pathEntity) : bool{
        if($pathEntity == null or count($pathEntity->points) != count($this->points))
            return false;
        else{
            foreach($this->points as $index => $point)
                if(!$point->equals($pathEntity->points[$index]))
                    return false;

            return true;
        }
    }

    /**
     * Returns true if the final PathPoint in the PathEntity is equal to Vector3 coords.
     *
     * @param Vector3 $vec
     * @return bool
     */
    public function isDestinationSame(Vector3 $vec) : bool{
        $pathpoint = $this->getFinalPathPoint();
        return $pathpoint == null ? false : $pathpoint->xCoord == (int)$vec->x && $pathpoint->zCoord == (int)$vec->z;
    }
}