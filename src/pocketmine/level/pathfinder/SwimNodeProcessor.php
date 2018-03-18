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

use pocketmine\block\Water;
use pocketmine\entity\behaviors\pathfinding\PathPoint;
use pocketmine\entity\Entity;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

class SwimNodeProcessor extends NodeProcessor{

    public  function getPathPointTo(Entity $entity) : PathPoint{
        return $this->openPoint(Math::floorFloat($entity->getBoundingBox()->minX), Math::floorFloat($entity->getBoundingBox()->minY + 0.5), Math::floorFloat($entity->getBoundingBox()->minZ));
    }

    public  function getPathPointToCoords(Entity $entity, float $x, float $y, float $target) : PathPoint{
        return $this->openPoint(Math::floorFloat($x - ($entity->width / 2.0)), Math::floorFloat($y + 0.5), Math::floorFloat($target - ($entity->width / 2.0)));
    }

    public  function findPathOptions(array $pathOptions, Entity $entity, PathPoint $currentPoint, PathPoint $targetPoint, float $maxDistance): int{
        $i = 0;

        $facing = [
            new Vector3(0, -1, 0),
            new Vector3(0, 1, 0),
            new Vector3(0, 0, -1),
            new Vector3(0, 0, 1),
            new Vector3(-1, 0, 0),
            new Vector3(1, 0, 0),
        ];

        foreach($facing as $f){
            $pathpoint = $this->getSafePoint($entity, $currentPoint->xCoord + $f->getX(), $currentPoint->yCoord + $f->getY(), $currentPoint->zCoord + $f->getZ());

            if($pathpoint != null && !$pathpoint->visited && $pathpoint->distanceTo($targetPoint) < $maxDistance){
                $pathOptions[$i++] = $pathpoint;
            }
        }

        return $i;
    }

    protected function getSafePoint(Entity $entity, int $x, int $y, int $z) : ?PathPoint{
        $i = $this->suyaDegiyorMu($entity, $x, $y, $z);
        return $i == -1 ? $this->openPoint($x, $y, $z) : null;
    }

    public function suyaDegiyorMu(Entity $entity, int $x, int $y, int $z) : int{
        for($i = $x; $i < $x + $this->entitySize->x; ++$i)
            for($j = $y; $j < $y + $this->entitySize->y; ++$j)
                for($k = $z; $k < $z + $this->entitySize->z; ++$k){
                    $block = $this->level->getBlock(new Vector3($i, $j, $k));

                    if (!($block instanceof Water))
                        return 0;

                }

        return -1;
    }
}