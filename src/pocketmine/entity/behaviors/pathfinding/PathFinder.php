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

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\pathfinder\NodeProcessor;
use pocketmine\math\Vector3;

class PathFinder{

    /** @var Path The path being generated */
    private $path;

    /** @var PathPoint[] Selection of path points to add to the path */
    private $pathOptions = [];
    /** @var NodeProcessor */
    private $nodeProcessor;

    public function __construct(NodeProcessor $nodeProcessor){
        $this->nodeProcessor = $nodeProcessor;
        $this->path = new Path();
    }

    public function createEntityPathTo(Level $level, Entity $entity, Vector3 $v, float $distance) : PathEntity{
        return $v instanceof Entity ? $this->createEntityPathToOriginal($level, $entity, $v->z, $v->getBoundingBox()->minY, $v->z, $distance) : $this->createEntityPathToOriginal($level, $entity, $v->getX() + 0.5, $v->getY() + 0.5, $v->getZ() + 0.5, $distance);
    }

    public function createEntityPathToOriginal(Level $level, Entity $entity, float $x, float $y, float $z, float $distance) : PathEntity{
        $this->path->clearPath();
        $this->nodeProcessor->initProcessor($level, $entity);
        $pathpoint = $this->nodeProcessor->getPathPointTo($entity);
        $pathpoint1 = $this->nodeProcessor->getPathPointToCoords($entity, $x, $y, $z);
        $pathentity = $this->addToPath($entity, $pathpoint, $pathpoint1, $distance);
        $this->nodeProcessor->postProcess();

        return $pathentity;
    }

    public function addToPath(Entity $entity, PathPoint $ppStart, PathPoint $ppEnd, float $maxDistance) : PathEntity{
        $ppStart->totalPathDistance = 0.0;
        $ppStart->distanceToNext = $ppStart->distanceToSquared($ppEnd);
        $ppStart->distanceToTarget = $ppStart->distanceToNext;
        $this->path->clearPath();
        $this->path->addPoint($ppStart);
        $pathpoint = $ppStart;

        while(!$this->path->isPathEmpty()){
            $pathpoint1 = $this->path->dequeue();

            if($pathpoint1->equals($ppEnd)){
                return $this->createEntityPath($ppStart, $ppEnd);
            }

            if($pathpoint1->distanceToSquared($ppEnd) < $pathpoint->distanceToSquared($ppEnd))
                $pathpoint = $pathpoint1;

            $pathpoint1->visited = true;
            $i = $this->nodeProcessor->findPathOptions($this->pathOptions, $entity, $pathpoint1, $ppEnd, $maxDistance);

            for($j = 0; $j < $i; ++$j){
                $pathpoint2 = $this->pathOptions[$j];
                $f = $pathpoint1->totalPathDistance + $pathpoint1->distanceToSquared($pathpoint2);

                if($f < $maxDistance * 2.0 && (!$pathpoint2->isAssigned() || $f < $pathpoint2->totalPathDistance)){
                    $pathpoint2->previous = $pathpoint1;
                    $pathpoint2->totalPathDistance = $f;
                    $pathpoint2->distanceToNext = $pathpoint2->distanceToSquared($ppEnd);

                    if($pathpoint2->isAssigned()){
                        $this->path->changeDistance($pathpoint2, $pathpoint2->totalPathDistance + $pathpoint2->distanceToNext);
                    }else{
                        $pathpoint2->distanceToTarget = $pathpoint2->totalPathDistance + $pathpoint2->distanceToNext;
                        $this->path->addPoint($pathpoint2);
                    }
                }
            }
        }

        return $pathpoint->equals($ppStart) ? null : $this->createEntityPath($ppStart, $pathpoint);
    }

    private function createEntityPath(PathPoint $ppStart, PathPoint $ppEnd) : PathEntity{
        $i = 1;

        for($pathpoint = $ppEnd; $pathpoint->previous != null; $pathpoint = $pathpoint->previous)
            ++$i;

        $app = [];
        $pathpoint1 = $ppEnd;
        --$i;

        for($app[$i] = $ppEnd; $pathpoint1->previous != null; $app[$i] = $pathpoint1){
            $pathpoint1 = $pathpoint1->previous;
            --$i;
        }

        return new PathEntity($app);
    }
}