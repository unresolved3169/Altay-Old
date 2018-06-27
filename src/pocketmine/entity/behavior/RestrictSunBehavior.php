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
	    if($this->isSunny() and $this->mob->level->canSeeSky($this->mob->floor())){
	        $this->mob->setOnFire(5);
	    }
	    return false;
	}

	public function isSunny() : bool{
		$degree = $this->mob->level->getSunAngleDegrees();
		return $degree > 15 and $degree < 165;
	}
}