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

use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class HurtByTargetBehavior extends FindAttackableTargetBehavior{
	
	public function canStart() : bool{
		$cause = $this->mob->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			if($cause->getDamager() instanceof Player){
				return true;
			}
		}
		return false;
	}
	
	public function onStart() : void{
	    $lastAttackCause = $this->mob->getLastAttackCause();
	    $lastAttackCause = $lastAttackCause !== null ? $lastAttackCause->getDamager() : null;
		$this->mob->setTargetEntity($lastAttackCause);
		parent::onStart();
	}
}