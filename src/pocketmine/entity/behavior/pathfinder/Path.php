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

class Path{
	
	/* @var Vector3[] */
	protected $vectors = [];
	
	public function __construct(array $vectors = []){
		$this->vectors = $vectors;
	}
	
	public static function findPath(Entity $entity, Vector3 $pos) : bool{
		$navigator = new EntityNavigator($entity);
		return new Path($navigator->navigate($pos));
	}
	
	public function havePath() : bool{
		return count($this->vectors) > 0;
	}
	
	public function getNextVector() : ?Vector3{
		return @array_shift($this->vectors);
	}
	
	public function getVector(int $index) ?Vector3{
		return $this->vectors[$index] ?? null;
	}
	
	public function setVector(int $index, Vector3 $vec) : void{
		$this->vectors[$index] = $vec;
	}
	
	public function getVectors() : array{
		return $this->vectors;
	}
	
	public function setVectors(array $vectors) : void{
		return $this->vectors = $vectors;
	}
}