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

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class EntityNavigator{
	
	/** @var Entity */
	protected $entity;
	
	public function __construct(Entity $entity){
		$this->entity = $entity;
	}
	
	public function navigate(Vector3 $pos) : array{
		$pathVectors = [];
		$lastPoint = end($pathVectors);
		while($lastPoint === null or $lastPoint->distance($pos) > 1){
			if($this->canGoToVector($pos, true) and $isclear = $this->isClearBetweenPoints($this->entity->asVector3(), $pos)){
				$points = $this->getClearBetweenPoints($this->entity, $pos);
				$pathVectors = array_merge($pathVectors, $points);
			}elseif(!$isclear){
				$points = $this->tryFindPath($pos);
				if(empty($points)){
					break;
				}
				$pathVectors = array_merge($pathVectors, $points);
			}
		}
		
		return $pathVectors;
	}
	
	public function tryFindPath(Vector3 $targetPos) : array
		$level = $this->entity->getLevel();
		$dist = $this->entity->distance($targetPos);
		$rayPos = $this->entity->asVector3();
		$bb = new AxisAlignedBB($rayPos->x - $dist, $rayPos->y - $dist, $rayPos->z - $dist, $rayPos->x + $dist, $rayPos->y + $dist, $rayPos->z + $dist);
		$bb2 = $bb->grow(1,1,1);
		$collides = [];
		for($z = floor($bb2->minZ); $z <= ceil($bb2->maxZ); ++$z){
			for($x = floor($bb2->minX); $x <= ceil($bb2->maxX); ++$x){
				for($y = floor($bb2->minY); $y <= ceil($bb2->maxY); ++$y){
					$block = $level->getBlockAt($x, $y, $z);
					if(!$block->isSolid() and $block->collidesWithBB($bb) and $this->canGoToVector($block) and $this->isClearBetweenPoints($block, $targetPos)){
						$collides[$targetPos->distance($block)] = $block;
					}
				}
			}
		}
	 return array_values(ksort($collides));
	}
	
	public function canGoToVector(Vector3 $pos, bool $canFall = false) : bool{
		$level = $entity->level;
		$val0 = $level->getBlock($pos->getSide(0))->isSolid();
		$val1 = $level->getBlock($pos->getSide(0, 2))->isSolid();
		
		return $val0 or ($canFall === true and $val1 === true);
	}
	
	public function isClearBetweenPoints(Vector3 $from,  Vector3 $to) : bool{
		$distance = $from->distance($to);
		$direction = $to->subtract($from)->normalize();
		$level = $this->entity->level;
		
		if($distance->length() < $direction->length()) return true;
		
		$rayPos = $this->entity->asVector3();
		
		while($distance > $this->entity->distance($rayPos)){
			if($level->getBlockAt(...$rayPos->toArray())->isSolid()) return false;
			$rayPos = $rayPos->add($direction);
		}
		
		return true;
	}
	
	public function getClearBetweenPoints(Vector3 $from,  Vector3 $to) : array{
		$distance = $from->distance($to);
		$direction = $to->subtract($from)->normalize();
		$level = $this->entity->level;
		
		if($distance->length() < $direction->length()) return true;
		
		$rayPos = $this->entity->asVector3();
		
		$points = [];
		
		while($distance > $this->entity->distance($rayPos)){
			if(!$level->getBlockAt(...$rayPos->toArray())->isSolid()){
				$points[] = $rayPos;
			}
			$rayPos = $rayPos->add($direction);
		}
		
		return $points;
	}
}