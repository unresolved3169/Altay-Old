<?php

namespace pocketmine\utils\navigator\providers;

use pocketmine\utils\navigator\Tile;

interface BlockedProvider{
	
	public function isBlocked(Tile $tile) : bool;
}