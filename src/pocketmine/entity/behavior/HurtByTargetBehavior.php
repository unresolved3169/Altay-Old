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
use pocketmine\Player;

class HurtByTargetBehavior extends FindAttackableTargetBehavior{

	public function canStart() : bool{
		$player = $this->getLastAttackSource();
		return $player instanceof Player and $player->isSurvival();
	}

	public function onStart(): void{
		$this->mob->setTargetEntity($this->getLastAttackSource());

		parent::onStart();
	}

	public function getLastAttackSource(): ?Entity{
		$cause = $this->mob->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent)
			return $cause->getDamager();

		return null;
	}
}