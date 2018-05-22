<?php

namespace pocketmine\entity\pathfinder;

use pocketmine\math\Vector2;

class PathPoint extends Vector2{
	
	public $fScore = 0;
	public $gScore = 0;
	
	public function getHashCode() : int{
		return ($this->x * 273) ^ $this->y;
	}
}