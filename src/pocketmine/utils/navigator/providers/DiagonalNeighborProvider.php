<?php

namespace pocketmine\utils\navigator\providers;

use pocketmine\utils\navigator\Tile;

class DiagonalNeighborProvider implements NeighborProvider{
	
	protected $neighbors = [
	[0,-1],
	[1,0],
	[0,1],
	[-1,0],
	[-1,-1],
	[1,-1],
	[1,1],
	[-1,1]
	];
	
	public function getNeighbors(Tile $tile) : array{
		$result = [];
		for($i = 0; $i < count($this->neighbors); $i++){
			$xy = $this->neighbors[$i];
			$result[] = new Tile($xy[0], $xy[1];
		}
		return $result;
	}
}