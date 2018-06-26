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

class EntityNavigator{

	/** @var Mob */
	protected $mob;

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

	public function __construct(Mob $mob){
		$this->mob = $mob;
	}

	public function navigate(PathPoint $from, PathPoint $to, float $followRange = 16.0) : array{
		$blockCache = [];
		$ticks = 0;
		$from->fScore = $this->calculateGridDistance($from, $to);
		$last = $from;
		$path = [];
		$open = [$from->getHashCode() => $from];
		$currentY = $this->getPathableY();
		$closed = [];
		$highScore = $from;

		while(!empty($open)){
			$current = $last;
			if($last !== $highScore){
				uasort($open, function($a,$b){
					if($a->fScore == $b->fScore) return 0;

					return $a->fScore > $b->fScore ? 1 : -1;
				});
				$current = reset($open);
				$currentY = $this->getBlockByPoint($current, $blockCache)->y;
			}

			$last = null;

			if($current->equals($to)){
				return $this->initPath($path, $current);
			}
			if($ticks++ > 50){
				return $this->initPath($path, $highScore);
			}

			unset($open[$current->getHashCode()]);
			$closed[$current->getHashCode()] = $current;

			foreach ($this->getNeighbors($current, $blockCache, $currentY) as $n){
				$blockPos = $this->getBlockByPoint($n, $blockCache);
				if(!isset($closed[$n->getHashCode()]) and $blockPos->distanceSquared($this->mob) <= $followRange){
					$g = $current->gScore + $this->calculateBlockDistance($current, $n, $blockCache);

					if(isset($open[$n->getHashCode()])){
						$og = $open[$n->getHashCode()];
						if($g >= $og->gScore) continue;
					}
					$open[$n->getHashCode()] = $n;
					$path[$n->getHashCode()] = $current;

					$n->gScore = $g;
					$n->fScore = $g + $this->calculateGridDistance($n, $to);

					if($n->fScore <= $highScore->fScore){
						$highScore = $n;
						$last = $n;
					}
				}
			}
			if($last !== null){
				$currentY = $this->getBlockByPoint($last, $blockCache)->y;
			}
		}

		return [];
	}

	public function getPathableY() : int{
		$last = (int) floor($this->mob->y);
		for($i = 1; $i < 3; $i++){
			if($this->mob->level->getBlock($this->mob->add(0 , -$i,0))->isSolid()){
				break;
			}
			$last--;
		}
		return $last;
	}

	public function initPath(array $path, PathPoint $current){
		$totalPath = [$current];
		while(isset($path[$current->getHashCode()])){
			$current = $path[$current->getHashCode()];
			array_unshift($totalPath, $current);
		}
		unset($totalPath[0]);
		return $totalPath;
	}

	public function calculateGridDistance(PathPoint $from, PathPoint $to) : float{
		return abs($from->x - $to->x) + abs($from->y - $to->y);
	}

	public function calculateBlockDistance(PathPoint $from, PathPoint $to, array $cache) : float{
		$block1 = $this->getBlockByPoint($from, $cache);
		$block2 = $this->getBlockByPoint($to, $cache);

		if($block1 === null or $block2 === null){
			return 0;
		}else{
			$block1 = $block1->asVector3();
			$block2 = $block2->asVector3();
		}

		if($this->mob->canClimb()){
			$block1->y = $block2->y = 0;
		}

		return $block1->distanceSquared($block2);
	}

	public function getBlockByPoint(PathPoint $tile, array $cache) : ?Block{
		return $cache[$tile->getHashCode()] ?? null;
	}

	/**
	 * @param PathPoint $tile
	 * @param array $cache
	 * @param int $startY
	 * @return Vector2[]
	 */
	public function getNeighbors(PathPoint $tile, array &$cache, int $startY) : array{
		$block = $this->mob->level->getBlock(new Vector3($tile->x, $startY, $tile->y));

		if(!isset($cache[$tile->getHashCode()])){
			$cache[$tile->getHashCode()] = $block;
		}

		$list = [];
		for ($index = 0; $index < count($this->neighbors); ++$index) {
			$item = new PathPoint($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);
			// Check for too high steps

			$coord = new Vector3((int)$item->x, $block->y, (int)$item->y);
			if ($this->mob->level->getBlock($coord)->isSolid()) {
				if ($this->mob->canClimb()) {
					$blockUp = $this->mob->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					$canMove = false;
					for($i = 0; $i < 10; $i++){
						if($this->isBlocked($blockUp->asVector3())){
							$blockUp = $this->mob->level->getBlock($blockUp->getSide(Vector3::SIDE_UP));
							continue;
						}

						$canMove = true;
						break;
					}

					if(!$canMove or $this->isObstructed($blockUp)) continue;

					$cache[$item->getHashCode()] = $blockUp;
				}else{
					$blockUp = $this->mob->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					if($blockUp->isSolid()){
						// Can't jump
						continue;
					}

					if($this->isObstructed($blockUp)) continue;

					$cache[$item->getHashCode()] = $blockUp;
				}
			}else{
				$blockDown = $this->mob->level->getBlock($coord->add(0, -1, 0));
				if(!$blockDown->isSolid()){
					if($this->mob->canClimb()){
						$canClimb = false;
						$blockDown = $this->mob->level->getBlock($blockDown->getSide(Vector3::SIDE_DOWN));
						for ($i = 0; $i < 10; $i++) {
							if (!$blockDown->isSolid()) {
								$blockDown = $this->mob->level->getBlock($blockDown->add(0, -1, 0));
								continue;
							}

							$canClimb = true;
							break;
						}

						if(!$canClimb) continue;

						$blockDown = $this->mob->level->getBlock($blockDown->getSide(Vector3::SIDE_UP));

						if($this->isObstructed($blockDown)) continue;

						$cache[$item->getHashCode()] = $blockDown;
					}else{
						if(!$this->mob->level->getBlock($coord->getSide(Vector3::SIDE_DOWN, 2))->isSolid()){
							// Will fall
							continue;
						}

						if ($this->isObstructed($blockDown)) continue;

						$cache[$item->getHashCode()] = $blockDown;
					}
				}else{
					if($this->isObstructed($coord)) continue;

					$cache[$item->getHashCode()] = $this->mob->level->getBlock($coord);
				}
			}

			$list[$index] = $item;
		}

		$this->checkDiagonals($list);
		return $list;
	}

	public function checkDiagonals(array &$list) : void{
		$checkDiagonals = [0 => [4,5], 1 => [5,6], 2 => [6,7], 3 => [4,7]];

		foreach($checkDiagonals as $index => $diagonal){
			if(!isset($list[$index])){
				foreach($diagonal as $dia){
					unset($list[$dia]);
				}
			}
		}
	}

	public function isObstructed(Vector3 $coord) : bool{
		for($i = 1; $i < $this->mob->height; $i++)
			if($this->isBlocked($coord->add(0, $i, 0))) return true;

		return false;
	}

	public function isBlocked(Vector3 $coord) : bool{
		$block = $this->mob->level->getBlock($coord);
		return $block->isSolid();
	}

	public function tryMoveTo(Vector3 $pos, float $speed, float $range): bool{
		$path = Path::findPath($this->mob, $pos, $range);

		if($path->havePath() and $next = $path->getNextTile($this->mob)){
			$this->mob->lookAt(new Vector3($next->x + 0.5, $this->mob->y, $next->y + 0.5));
			$this->mob->moveForward($speed);

			return true;
		}

		return false;
	}

}