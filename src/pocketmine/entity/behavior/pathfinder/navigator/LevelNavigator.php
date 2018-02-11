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

namespace pocketmine\entiy\behavior\navigator;

use pocketmine\utils\navigator\Tile;
use pocketmine\utils\navigator\providers\BlockedProvider;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\entity\Entity;

class LevelNavigator implements BlockedProvider{
	
	private $blockCache = [];
	private $entity;
	private $entityPos;
	private $level;
	private $distance = 0;
	private $entityCoords = [];
	
	public function __construct(Entity $entity, Level $level, float $distance, array $blockCache, array $entityCoords){
		$this->entity = $entity;
		$this->entityPos = $entity->asVector3();
		$this->level = $level;
		$this->distance = $distance;
		$this->blockCache = $blockCache;
		$this->entityCoords = $entityCoords;
	}
	
	public function isObstructed(Vector3 $coord) : bool{
		for($i = 1; $i < $this->entity->height; $i++){
			if($this->isBlocked($coord->add(0,$i,0))) return true;
		}
		return false;
	}
	
	public function isBlocked($coord) : bool{
		if($coord instanceof Tile){
			$block = $this->blockCache[$coord->__toString()] ?? null;
			if($block === null) return true;

			if($block->isSolid()) return true;
			if (in_array($block->asVector3(), $this->entityCoords)) return true;

			$entityPos = new Vector2($this->entityPos->x, $this->entityPos->z);
			$tilePos = new Vector2((float) $coord->x, (float) $coord->y);

			if (Vector2.Distance(entityPos, tilePos) > _distance) return true;

			$blockCoordinates = $block->asVector3();

			if ($this->isObstructed($blockCoordinates)) return true;

			return false;
		}elseif($coord instanceof Tile){
		 $block = $this->level->getBlock($coord);
		
		 if($block === null or $block->isSolid()){
			 return true;
		 }
		 return false;
		}
		return false;
	}
}