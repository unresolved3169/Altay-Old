<?php

namespace pocketmine\utils\navigator\providers;

use pocketmine\utils\navigator\Tile;

class EmptyBlockedProvider implements BlockedProvider{
	
	public function isBlocked(Tile $tile) : bool{
		return false;
	}
}