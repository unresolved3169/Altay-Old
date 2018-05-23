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

namespace pocketmine\entity\behavior;

use pocketmine\level\Level;

class RestrictSunBehavior extends Behavior{

	public function canStart() : bool{
		if($this->isSunny() and !$this->mob->isOnFire() and $this->mob->level->getHighestBlockAt((int) $this->mob->x, (int) $this->mob->z) < $this->mob->y){
			$this->mob->setOnFire(3);
			return true;
		}

		return false;
	}

	public function isSunny() : bool{
		$time = $this->mob->level->getTime();
		return $time < Level::TIME_NOON or $time < Level::TIME_NIGHT or $time > Level::TIME_SUNRISE;
	}
}