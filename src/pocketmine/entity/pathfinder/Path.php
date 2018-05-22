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

namespace pocketmine\entity\pathfinder;

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\level\Level;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;

class Path{

	/* @var Vector2[] */
	protected $tiles = [];

	public function __construct(array $tiles = []){
		$this->tiles = $tiles;
	}

	public static function findPath(Mob $mob, Vector3 $targetPos, int $maxAttempt = 200) : Path{
		$from = new PathPoint($mob->x, $mob->z);
		$to = new PathPoint($targetPos->x, $targetPos->z);
	 
		$cache = [];

		return new Path($mob->getNavigator()->navigate($from, $to, $maxAttempt, $cache));
	}

	public function havePath() : bool{
		return !empty($this->tiles);
	}

	public function getNextTile(Entity $entity, bool $compressPath = false) : ?PathPoint{
		if($this->havePath()){
			$next = reset($this->tiles);

			if((int) $next->x === floor($entity->x) and (int) $next->y === floor($entity->z)){
				array_shift($this->tiles);

				return $this->getNextTile($entity);
			}

			if($compressPath){
			    foreach ($this->tiles as $tile){
			        if($this->isClearBetweenPoints($entity->level, $entity->asVector3(), new Vector3($tile->x, floor($entity->y), $tile->y))) {
                        $next = $tile;
                        unset($this->tiles[array_search($tile, $this->tiles)]);
                    }else{
			            break;
                    }
                }
            }
			return $next;
		}
		return null;
	}

    public function isClearBetweenPoints(Level $level, Vector3 $from, Vector3 $to) : bool{
        $entityPos = $from;
        $targetPos = $to;
        $distance = $entityPos->distance($targetPos);
        $rayPos = $entityPos;
        $direction = $targetPos->subtract($entityPos)->normalize();

        if ($distance < $direction->length()){
            return true;
        }

        do{
            if ($level->getBlock($rayPos)->isSolid()){
                return false;
            }
            $rayPos = $rayPos->add($direction);
        }while($distance > $entityPos->distance($rayPos));

        return true;
    }
}