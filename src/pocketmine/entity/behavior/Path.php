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

namespace pocketmine\entity\behavior;

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;

class Path{

	/* @var Vector2[] */
	protected $tiles = [];

	public function __construct(array $tiles = []){
		$this->tiles = $tiles;
	}

	public static function findPath(Mob $mob, Vector3 $targetPos, int $maxAttempt = 3) : Path{
		$from = new Vector2($mob->x, $mob->z);
		$to = new Vector2($targetPos->x, $targetPos->z);
		$blockCache = [$to->floor()->__toString() => $mob->level->getBlock($targetPos)];

		return new Path($mob->getNavigator()->navigate($from, $to, $maxAttempt, $blockCache));
	}

	public function havePath() : bool{
		return !empty($this->tiles);
	}

	public function getNextTile(Entity $entity) : ?Vector2{
		if($this->havePath()){
			$next = reset($this->tiles);

			if($next->x === $entity->x and $next->y === $entity->z){
				array_shift($this->tiles);
			}

			return $next;
		}
		return null;
	}
}