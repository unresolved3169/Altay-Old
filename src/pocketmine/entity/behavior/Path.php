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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\behavior\navigator\algorithms\BlockDistanceAlgorithm;
use pocketmine\entity\behavior\navigator\providers\LevelNavigator;
use pocketmine\entity\behavior\navigator\algorithms\ManhattanHeuristicAlgorithm;
use pocketmine\entity\behavior\navigator\providers\BlockDiagonalNeighborProvider;
use pocketmine\entity\behavior\navigator\Tile;
use pocketmine\entity\behavior\navigator\TileNavigator;
use pocketmine\math\Vector3;

class Path{
	
	/* @var Tile[] */
	protected $tiles = [];
	protected $blockCache;
	
	public function __construct(\stdClass $blockCache, array $tiles = []){
		$this->tiles = $tiles;
		$this->blockCache = $blockCache;
	}
	
	public static function findPath(Entity $source, Vector3 $target, float $distance) : Path{
		$blockCache = new \stdClass;
		try
		{
			$entityCoords = [];
			foreach($source->level->getEntities() as $entry)
			{
				$position = $entry->asVector3();
				if($position === $target) continue;

				$entityCoords[] = $position;
			}
				
			$level = $source->level;

			$navigator = new TileNavigator(
				new LevelNavigator($source, $level, $distance, $blockCache, $entityCoords),
				new BlockDiagonalNeighborProvider($level, (int) $source->y, $blockCache, $source),
				new BlockDistanceAlgorithm($blockCache, $source->canClimb()),
				new ManhattanHeuristicAlgorithm()
			);

			$targetPos = $target;
			$sourcePos = $source->asVector3();
			$from = new Tile((int) $sourcePos->x, (int) $sourcePos->z);
			$to = new Tile((int) $targetPos->x, (int) $targetPos->z);
				
			$path = $navigator->navigate($from, $to, 200);
			$resultPath = new Path($blockCache, $path);
		}
		catch(\Exception $e)
		{
			throw $e;
		}

		return $resultPath;
	}
	
	public function havePath() : bool{
		return !empty($this->tiles);
	}
	
	public function getNextTile(Entity $entity) : ?Tile{
		if($this->havePath()){
			$next = reset($this->tiles);
			
		//	if($next->x === floor($entity->x) and $next->y === floor($entity->z)){
				unset($this->tiles[array_search($next, $this->tiles)]);
				
				//if(!$this->getNextTile($entity)) return null;
		//	}
			
			return $next;
		}
		return null;
	}
	
	public function getBlock(Tile $tile) : ?Block{
		return $this->blockCache->{$tile->__toString()} ?? null;
	}
}