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
use pocketmine\entity\Entity;

class Path{
	
	/* @var Vector3[] */
	protected $vecs = [];
	protected $navigator;
	
	public function __construct(Entity $entity, array $vecs){
		$this->vecs = $vecs;
		$this->navigator = new EntitiyNavigator();
		$this->navigator->entity = $entity;
	}
	
	public static function findPath(Block $pos) : bool{
		//TODO
	}
	
	public function havePath() : bool{
		return count($this->vecs) > 0;
	}
	
	public function getNextVector() : ?Vector3{
		return @array_shift($this->vecs);
	}
}