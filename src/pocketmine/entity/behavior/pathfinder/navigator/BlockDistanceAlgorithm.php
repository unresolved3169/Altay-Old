<?php

namespace pocketmine\entiy\behavior\navigator;

use pocketmine\utils\navigator\Tile;
use pocketmine\utils\navigator\algorithms\DistanceAlgorithm;

class BlockDistanceAlgorithm implements DistanceAlgorithm{
	
	private $blockCache = [];
	private $canClimb = false;
	
	public function __construct(array $cache, bool $canClimb){
		$this->blockCache = $cache;
		$this->canClimb = $canClimb;
	}
	
	public function calculate(Tile $from, Tile $to) : int{
		$vFrom = $this->getBlock($from);
		$vTo = $this->getBlock($to);
		
		if($this->canClimb){
			$vFrom->y = 0;
			$vTo->y = 0;
		}
		
		return $vFrom->distance($vTo);
	}
	
	public function getBlock(Tile $tile){
		return $this->blockCache[$tile->__toString()] ?? null;
	}
}