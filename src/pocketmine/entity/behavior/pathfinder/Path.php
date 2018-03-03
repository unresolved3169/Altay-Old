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

namespace pocketmine\entity\behavior\pathfinder;

use pocketmine\math\Vector3;
use pocketmine\utils\navigator\{TileNavigator, Tile};
use pocketmine\utils\navigator\algorithms\ManhattanHeuristicAlgorithm;
use pocketmine\entity\behavior\pathfinder\navigator\{BlockDistanceAlgorithm, LevelNavigator, BlockDiagonalNeighborProvider};
use pocketmine\entity\Entity;
use pocketmine\block\Block;

class Path{
	
	/* @var Tile[] */
	protected $tiles = [];
	protected $blockCache = [];
	
	public function __construct(array $blockCache = [], array $tiles = []){
		$this->tiles = $tiles;
		$this->blockCache = $blockCache;
	}
	
	public static function findPath(Entity $source, Vector3 $target, float $distance, array $blockCache = []) : Path{
		try{
			$entityCoords = [];
			foreach($source->level->getEntities() as $entry) {
				$position = $entry->asVector3();
				if($position === $target) continue;

				$entityCoords[] = $position;
			}
				
			$level = $source->level;

			// FIXME : Emre verdiğin değer ve TileNavigator constant eşleşmiyor
			$navigator = new TileNavigator(
				new LevelNavigator($source, $level, $distance, $blockCache, $entityCoords),
				new BlockDiagonalNeighborProvider($level, $source->y, $blockCache, $source),
				new BlockDistanceAlgorithm($blockCache, $source->canClimb()),
				new ManhattanHeuristicAlgorithm()
			);

			$targetPos = $target;
			$sourcePos = $source->asVector3();
			$from = new Tile($sourcePos->x, $sourcePos->z);
			$to = new Tile($targetPos->x, $targetPos->z);
				
			$path = $navigator->navigate($from, $to, 200) ?? [];
				
			$resultPath = new Path($blockCache, $path);
		}catch(\Exception $e){
			throw $e;
		}

		return $resultPath;
	}
	
	public function havePath() : bool{
		return count($this->tiles) > 0;
	}
	
	public function getNextTile(Entity $entity) : ?Tile{
		if($this->havePath()){
			$next = array_shift($this->tiles);
			
			if($next->x === $entity->x and $next->y === $entity->z){ // wtf?!
				unset($this->tiles[array_search($next, $this->tiles)]);
				
				if(!$this->getNextTile($entity)) return null;
			}
			
			return $next;
		}
		return null;
	}
	
	public function getBlock(Tile $tile) : ?Block{
		return $this->blockCache[$tile->__toString()] ?? null;
	}
}