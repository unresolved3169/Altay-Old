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

use pocketmine\entity\Mob;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\block\Liquid;

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

	public function __construct(Mob $entity){
		$this->entity = $entity;
		$this->level = $entity->getLevel();
	}

	public function navigate(PathPoint $from, PathPoint $to, int $maxTick = 200, array $blockCache) : array{
		$this->level = $this->entity->getLevel(); //for level update
		$ticks = 0;
		$from->fScore = $this->calculateGridDistance($from, $to);
		$current = $from;
		$path = [];
		$open = [$from->__toString() => $from];
		$currentY = (int) $this->getPathableY($this->entity->y);
		$closed = [];

		while(!empty($open)){
			$currentScore = PHP_INT_MAX;
			$result = null;

			unset($open[$current->__toString()]);
			$closed[$current->__toString()] = $current;

			foreach ($this->getNeighbors($current, $blockCache, $currentY) as $n){
				if(!isset($closed[$n->__toString()]) and !isset($open[$n->__toString()])){
					$open[$n->__toString()] = $n;

					$g = $current->gScore + $this->calculateBlockDistance($current, $n, $blockCache);

					$n->gScore = $g;
					$n->fScore = $g + $this->calculateGridDistance($n, $to);

					if($n->fScore <= $currentScore){
						$currentScore = $n->fScore;
						$result = $n;
					}
				}
			}

			if($result instanceof PathPoint){
				$current = $result;
				$path[] = $current;
			}else{
				usort($open, function($a,$b){
					if($a->fScore == $b->fScore) return 0;

					return $a->fScore > $b->fScore ? 1 : -1;
				});
				$current = reset($open);
			}

			$currentY = $this->getBlockByPoint($current, $blockCache)->y;

			if($current->floor()->equals($to->floor()) or $ticks++ >= $maxTick){
				return $path;
			}
		}

		return $path;
	}

	public function calculateGridDistance(Vector2 $from, Vector2 $to) : float{
		return abs($from->x - $to->x) + abs($from->y - $to->y);
	}

	public function calculateBlockDistance(Vector2 $from, Vector2 $to, array $cache) : float{
		$block1 = $this->getBlockByPoint($from, $cache);
		$block2 = $this->getBlockByPoint($to, $cache);

		if($block1 === null or $block2 === null){
			return 0;
		}else{
			$block1 = $block1->asVector3();
			$block2 = $block2->asVector3();
		}

		if($this->entity->canClimb()){
			$block1->y = $block2->y = 0;
		}

		return $block1->distance($block2);
	}

	public function getBlockByPoint(Vector2 $tile, array $cache) : ?Block{
		return $cache[$tile->__toString()] ?? null;
	}

	public function getPathableY(float $y) : float{
		$pos = $this->entity->asVector3();
		for($i = 1; $i < 5; $i++){
			$b = $this->level->getBlock($pos->getSide(Vector3::SIDE_DOWN, $i));
			if($b instanceof Liquid or $b->isSolid()){
				return $y;
			}else{
				$y--;
			}
		}
		return $y;
	}

	/**
	 * @param Vector2 $tile
	 * @param array $cache
	 * @param int $startY
	 * @return Vector2[]
	 */
	public function getNeighbors(Vector2 $tile, array &$cache, int $startY) : array{
		$block = $this->level->getBlock(new Vector3($tile->x, $startY, $tile->y));

		if(!isset($cache[$tile->__toString()])){
			$cache[$tile->__toString()] = $block;
		}

		$list = [];
		for ($index = 0; $index < count($this->neighbors); ++$index) {
			$item = new Vector2($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);
			// Check for too high steps

			$coord = new Vector3((int)$item->x, $block->y, (int)$item->y);
			if ($this->level->getBlock($coord)->isSolid()) {
				if ($this->entity->canClimb()) {
					$blockUp = $this->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					$canMove = false;
					for ($i = 0; $i < 10; $i++) {
						if ($this->isBlocked($blockUp->asVector3())) {
							$blockUp = $this->level->getBlock($blockUp->getSide(Vector3::SIDE_UP));
							continue;
						}

						$canMove = true;
						break;
					}

					if (!$canMove or $this->isObstructed($blockUp)) continue;

					$cache[$item->__toString()] = $blockUp;
				} else {
					$blockUp = $this->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					if ($blockUp->isSolid()) {
						// Can't jump
						continue;
					}

					if ($this->isObstructed($blockUp)) continue;

					$cache[$item->__toString()] = $blockUp;
				}
			} else {
				$blockDown = $this->level->getBlock($coord->add(0, -1, 0));
				if (!$blockDown->isSolid()) {
					if ($this->entity->canClimb()) {
						$canClimb = false;
						$blockDown = $this->level->getBlock($blockDown->getSide(Vector3::SIDE_DOWN));
						for ($i = 0; $i < 10; $i++) {
							if (!$blockDown->isSolid()) {
								$blockDown = $this->level->getBlock($blockDown->add(0, -1, 0));
								continue;
							}

							$canClimb = true;
							break;
						}

						if (!$canClimb) continue;

						$blockDown = $this->level->getBlock($blockDown->getSide(Vector3::SIDE_UP));

						if ($this->isObstructed($blockDown)) continue;

						$cache[$item->__toString()] = $blockDown;
					} else {
						if (!$this->level->getBlock($coord->getSide(Vector3::SIDE_DOWN, 2))->isSolid()) {
							// Will fall
							continue;
						}

						if ($this->isObstructed($blockDown)) continue;

						$cache[$item->__toString()] = $blockDown;
					}
				} else {
					if ($this->isObstructed($coord)) continue;

					$cache[$item->__toString()] = $this->level->getBlock($coord);
				}
			}

			$list[] = $item;
		}

		$this->checkDiagonals($block, $list);

		return $list;
	}

	public function checkDiagonals(Block $block, array &$list){ // TODO: Improve this
		$pos = $block->asVector3();

		$checkDiagonals = [
			Vector3::SIDE_NORTH => [Vector3::SIDE_EAST, Vector3::SIDE_WEST],
			Vector3::SIDE_SOUTH => [Vector3::SIDE_EAST, Vector3::SIDE_WEST],
			Vector3::SIDE_EAST => [Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH],
			Vector3::SIDE_WEST => [Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH]
		];

		foreach($checkDiagonals as $index => $diagonal){
			$posNew = $pos->getSide($index);
			if(!in_array($this->getTileFromPos($posNew), $list)){
				foreach($diagonal as $dia){
					unset($list[array_search($this->getTileFromPos($posNew->getSide($dia)), $list)]);
				}
			}
		}
	}

	public function getTileFromPos(Vector3 $coord) : Vector2{
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
		$path = Path::findPath($this->entity, $pos, 1);

		if($path->havePath() and $next = $path->getNextTile($this->entity)){
			$this->entity->lookAt(new Vector3($next->x + 0.5, $this->entity->y, $next->y + 0.5));
			$this->entity->moveForward($speed);

			return true;
		}

		return false;
	}

}