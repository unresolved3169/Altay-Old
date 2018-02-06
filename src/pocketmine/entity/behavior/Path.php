<?php

namespace pocketmine\entity\behavior;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

class Path{
	
	/* @var Vector3[] */
	protected $vecs = [];
	
	public function __construct(array $vecs){
		$this->vecs = $vecs;
	}
	
	public static function findPath(Block $pos) : Path{
		$d = rand(0,3);
		$vecs = [];
		$step = 1;
		$limit = rand(5,8);
		while(!$pos->getSide($d, $step)->isSolid() and count($vecs) < $limit){
			$step++;
			$vecs[] = $pos->getSide($d, $step)->asVector3();
		}
		
		return new Path($vecs);
	}
	
	public function havePath() : bool{
		return count($this->vecs) > 0;
	}
	
	public function getNextVector() : ?Vector3{
		return @array_shift($this->vecs);
	}
}