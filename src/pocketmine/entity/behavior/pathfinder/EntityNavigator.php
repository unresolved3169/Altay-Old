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
	public $entity;
	
	public function getRotations() : array{
		return [2,3,4,5];
	}
	
	public function navigate(Vector3 $pos) : array{
		$fail = 0;
		$rots = $this->sortRotations($pos, $this->getRotations());
		while($fail < 4){
			$find = $this->tryFindPath($pos, next($rots));
			if($find !== null){
				return $find;
			}else{
				$fail++;
			}
		}
		
		return [];
	}
	
	public function tryFindPath(Vector3 $pos, int $rot) : ?array{
		$result = [];
		while($this->canGoToVector($this->entity->asVector3()->getSide($rot))){
			//TODO
		}
		
		return empty($result) ? null : $result;
	}
	
	public function canGoToVector(Vector3 $pos) : bool{
		$level = $entity->level;
		return $level->getBlock($pos->getSide(0))->isSolid() or $level->getBlock($pos->getSide(0, 2))->isSolid();
	}
	
	public function sortRotations(Vector3 $pos, array $rots) : array{
		$sort = [];
		foreach($rots as $rot){
			$sort[$rot] = $pos->distance($this->entity->level->getBlock($this->entity)->getSide($rot));
		}
		return array_keys(asort($sort));
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
}