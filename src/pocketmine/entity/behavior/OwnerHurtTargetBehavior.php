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

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class OwnerHurtTargetBehavior extends Behavior{

	public function canStart() : bool{
		$owner = $this->mob->getOwningEntity();

		if($owner !== null){
			$this->mob->setTargetEntity($this->getLastAttackSource());
			return true;
		}

		return false;
	}

	public function getLastAttackSource(): ?Entity{
		$cause = $this->mob->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent)
			return $cause->getDamager();

		return null;
	}

	public function canContinue() : bool{
		return false;
	}
}