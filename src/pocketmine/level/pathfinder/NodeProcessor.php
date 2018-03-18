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

namespace pocketmine\level\pathfinder;

use pocketmine\entity\behaviors\pathfinding\PathPoint;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

abstract class NodeProcessor{

    /** @var Level */
    protected $level;
    /** @var PathPoint[] */
    protected $pointMap = [];
    /** @var Vector3 */
    protected $entitySize;

    public function initProcessor(Level $level, Entity $entity) : void{
        $this->level = $level;
        $this->pointMap = [];
        $this->entitySize = new Vector3(
            Math::floorFloat($entity->width + 1.0),
            Math::floorFloat($entity->height + 1.0),
            Math::floorFloat($entity->width + 1.0)
        );
    }

    /**
     * This method is called when all nodes have been processed and PathEntity is created.
     */
    public function postProcess() : void{}

    protected function openPoint(int $x, int $y, int $z) : PathPoint{
        $i = PathPoint::makeHash($x, $y, $z);

        if(empty($this->pointMap[$i])){
            $this->pointMap[$i] = new PathPoint($x, $y, $z);
        }

        return $this->pointMap[$i];
    }

    /**
     * Returns given entity's position as PathPoint
     *
     * @param Entity $entity
     * @return PathPoint
     */
    public abstract function getPathPointTo(Entity $entity) : PathPoint;

    /**
     * Returns PathPoint for given coordinates
     *
     * @param Entity $entity
     * @param float $x
     * @param float $y
     * @param float $target
     * @return PathPoint
     */
    public abstract function getPathPointToCoords(Entity $entity, float $x, float $y, float $target) : PathPoint;

    /**
     * @param PathPoint[] $pathOptions
     * @param Entity $entity
     * @param PathPoint $currentPoint
     * @param PathPoint $targetPoint
     * @param float $maxDistance
     * @return int
     */
    public abstract function findPathOptions(array $pathOptions, Entity $entity, PathPoint $currentPoint, PathPoint $targetPoint, float $maxDistance) : int;

}