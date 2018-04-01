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

use pocketmine\entity\Mob;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class EntityNavigator{

	/** @var Mob */
	protected $entity;
	/** @var \pocketmine\level\Level */
	protected $level;

	protected $neighbors = [
		[0, -1],
		[1, 0],
		[0, 1],
		[-1, 0],
		[-1, -1],
		[1, -1],
		[1, 1],
		[-1, 1]
	];

	/**
	 * EntityNavigator constructor.
	 * @param Mob $entity
	 */
	public function __construct(Mob $entity){
		$this->entity = $entity;
		$this->level = $entity->getLevel();
	}


	/**
	 * @param Vector2 $from
	 * @param Vector2 $to
	 * @param int $maxAttempt
	 * @return array
	 */
	public function navigate(Vector2 $from, Vector2 $to, int $maxAttempt = 200): array{
		$attempt = 0;
		$current = $to;
		$path = [];

		while(!$current->equals($from) and ++$attempt < $maxAttempt){
			/** @var Vector2 $last */
			$last = null;
			foreach($this->getNeighbors($current) as $tile){
				if($last === null or $last->distance($from) >= $tile->distance($from)){
					$last = $tile;
				}
			}

			if($last !== null){
				$path[] = $last;
				$current = $last;
			}else{
				break;
			}
		}

		$path = array_reverse($path);

		unset($path[0]);

		$path[] = $to;

		return $path;
	}

	/**
	 * @param Vector2 $tile
	 * @return Vector2[]
	 */
	public function getNeighbors(Vector2 $tile) : array{
		$block = $this->level->getBlock(new Vector3($tile->x, $this->entity->y, $tile->y));

		$list = [];
		for($index = 0; $index < count($this->neighbors); ++$index){
			$item = new Vector2($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);

			// Check for too high steps

			$coord = new Vector3((int)$item->x, $block->y, (int)$item->y);
			if($this->level->getBlock($coord)->isSolid()){
				if($this->entity->canClimb()){
					$blockUp = $this->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					$canMove = false;
					for($i = 0; $i < 10; $i++){
						if($this->isBlocked($blockUp->asVector3())){
							$blockUp = $this->level->getBlock($blockUp->getSide(Vector3::SIDE_UP));
							continue;
						}

						$canMove = true;
						break;
					}

					if(!$canMove or $this->isObstructed($blockUp)) continue;
				}else{
					$blockUp = $this->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					if($blockUp->isSolid()){
						// Can't jump
						continue;
					}

					if($this->isObstructed($blockUp)) continue;
				}
			}else{
				$blockDown = $this->level->getBlock($coord->add(0, -1, 0));
				if(!$blockDown->isSolid()){
					if($this->entity->canClimb()){
						$canClimb = false;
						$blockDown = $this->level->getBlock($blockDown->getSide(Vector3::SIDE_DOWN));
						for($i = 0; $i < 10; $i++){
							if(!$blockDown->isSolid()){
								$blockDown = $this->level->getBlock($blockDown->add(0, -1, 0));
								continue;
							}

							$canClimb = true;
							break;
						}

						if(!$canClimb) continue;

						$blockDown = $this->level->getBlock($blockDown->getSide(Vector3::SIDE_UP));

						if($this->isObstructed($blockDown)) continue;
					}else{
						if(!$this->level->getBlock($coord->getSide(Vector3::SIDE_DOWN, 2))->isSolid()) {
							// Will fall
							continue;
						}

						if($this->isObstructed($blockDown)) continue;
					}
				}elseif($this->isObstructed($coord)) continue;
			}

			$list[] = $item;
		}

		$this->checkDiagonals($block, $list);

		return $list;
	}

	public function checkDiagonals(Block $block, array &$list){
		$pos = $block->asVector3();

		$posNorth = $pos->getSide(Vector3::SIDE_NORTH);
		if(!in_array($this->getTileFromBlock($posNorth), $list)){
			unset($list[array_search($this->getTileFromBlock($posNorth->getSide(Vector3::SIDE_EAST)), $list)]);
			unset($list[array_search($this->getTileFromBlock($posNorth->getSide(Vector3::SIDE_WEST)), $list)]);
		}

		$posSouth = $pos->getSide(Vector3::SIDE_SOUTH);
		if(!in_array($this->getTileFromBlock($posSouth), $list)){
			unset($list[array_search($this->getTileFromBlock($posSouth->getSide(Vector3::SIDE_EAST)), $list)]);
			unset($list[array_search($this->getTileFromBlock($posSouth->getSide(Vector3::SIDE_WEST)), $list)]);
		}

		$posEast = $pos->getSide(Vector3::SIDE_EAST);
		if(!in_array($this->getTileFromBlock($posEast), $list)){
			unset($list[array_search($this->getTileFromBlock($posEast->getSide(Vector3::SIDE_NORTH)), $list)]);
			unset($list[array_search($this->getTileFromBlock($posEast->getSide(Vector3::SIDE_SOUTH)), $list)]);
		}

		$posWest = $pos->getSide(Vector3::SIDE_WEST);
		if(!in_array($this->getTileFromBlock($posWest), $list)){
			$pos1 = $posWest->getSide(Vector3::SIDE_NORTH);
			unset($list[array_search($this->getTileFromBlock($pos1), $list)]);
			unset($list[array_search($this->getTileFromBlock($pos1), $list)]);
		}
	}

	public function getTileFromBlock(Vector3 $coord) : Vector2{
		return new Vector2($coord->x, $coord->z);
	}

	public function isObstructed(Vector3 $coord) : bool{
		for($i = 1; $i < $this->entity->height; $i++)
			if($this->isBlocked($coord->add(0, $i, 0))) return true;

		return false;
	}

	public function isBlocked(Vector3 $coord) : bool{
		$block = $this->level->getBlock($coord);
		return $block->isSolid();
	}

	public function tryMoveTo(Vector3 $pos, float $speed): bool{
		$path = Path::findPath($this->entity, $pos);

		if($path->havePath() and $next = $path->getNextTile($this->entity)){
			$this->entity->lookAt(new Vector3($next->x + 0.5, $this->entity->y, $next->y + 0.5));
			$this->entity->moveForward($speed);

			return true;
		}

		return false;
	}

}