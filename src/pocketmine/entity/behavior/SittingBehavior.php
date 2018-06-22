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
use pocketmine\entity\Mob;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class SittingBehavior extends Behavior{

	// TODO : Change to Wolf
	public function __construct(Mob $mob){
		parent::__construct($mob);
	}

	public function canStart(): bool{
		if(!$this->mob->getGenericFlag(Entity::DATA_FLAG_TAMED)) return false;
		if(!$this->mob->getGenericFlag(Entity::DATA_FLAG_BREATHING)) return false;

		$owner = $this->mob->getOwningEntity();

		$shouldStart = $owner == null || ((!($this->mob->distance($owner) < 144.0) || $this->getLastAttackSource() == null) && $this->mob->getGenericFlag(Entity::DATA_FLAG_SITTING));
		if(!$shouldStart) return false;

		$this->mob->setMotion($this->mob->getMotion()->multiply(0, 1.0, 0.0));

		return true;
	}

	public function canContinue(): bool{
		return $this->mob->getGenericFlag(Entity::DATA_FLAG_SITTING);
	}

	public function onEnd(): void{
		$this->mob->setGenericFlag(Entity::DATA_FLAG_SITTING, false);
	}

	public function getLastAttackSource(): ?Entity{
		$cause = $this->mob->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent)
			return $cause->getDamager();

		return null;
	}

}