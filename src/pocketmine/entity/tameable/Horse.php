<?php

/*
 *
 *    _______                    _
 *   |__   __|                  (_)
 *      | |_   _ _ __ __ _ _ __  _  ___
 *      | | | | | '__/ _` | '_ \| |/ __|
 *      | | |_| | | | (_| | | | | | (__
 *      |_|\__,_|_|  \__,_|_| |_|_|\___|
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Turanic
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\tameable;

use pocketmine\entity\Animal;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\item\Item as ItemItem;

use pocketmine\entity\behavior\{StrollBehavior, RandomLookaroundBehavior, LookAtPlayerBehavior, PanicBehavior};

class Horse extends Animal{
	const NETWORK_ID = self::HORSE;

	public $width = 0.3;
	public $height = 0;

	public $drag = 0.2;
	public $gravity = 0.3;

	const CREAMY = 0;
	const WHITE = 1;
	const BROWN = 2;
	const GRAY = 3;
	const BLACK = 4;

	public function initEntity(){
		$this->addBehavior(new PanicBehavior($this, 0.25, 2.0));
		$this->addBehavior(new StrollBehavior($this));
		$this->addBehavior(new LookAtPlayerBehavior($this));
		$this->addBehavior(new RandomLookaroundBehavior($this));
		$this->propertyManager->setInt(Entity::DATA_VARIANT, rand(0, 4));
        $this->setMaxHealth(30);
		parent::initEntity();
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Horse";
	}

	/**
	 * @param $id
	 */
	public function setChestPlate($id){
		/*	
		416, 417, 418, 419 only
		*/
		$pk = new MobArmorEquipmentPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->slots = [
			ItemItem::get(0, 0),
			ItemItem::get($id, 0),
			ItemItem::get(0, 0),
			ItemItem::get(0, 0)
		];
		foreach($this->level->getPlayers() as $player){
			$player->dataPacket($pk);
		}
	}

    public function getXpDropAmount(): int{
        return !$this->isBaby() ? mt_rand(1,3) : 0;
    }

}
