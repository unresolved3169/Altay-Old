<?php

namespace pocketmine\utils\navigator\providers;

use pocketmine\utils\navigator\Tile;

interface NeighborProvider{
	
	public function getNeighbors(Tile $tile) : array;
}