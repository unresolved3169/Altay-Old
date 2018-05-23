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

namespace pocketmine\entity\hostile;

use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\behavior\FindAttackableTargetBehavior;
use pocketmine\entity\behavior\HurtByTargetBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\Monster;
use pocketmine\inventory\EntityEquipment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;

class Skeleton extends Monster{
	public const NETWORK_ID = self::SKELETON;

	public $width = 0.6;
	public $height = 1.95;

	/** @var EntityEquipment */
	protected $equipment;

	// speed 0.25
	protected function initEntity() : void{
		$this->setMaxHealth(20);
		$this->equipment = new EntityEquipment($this);
		$this->equipment->setItemInHand(ItemFactory::get(Item::BOW));
		parent::initEntity();
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);
		$this->equipment->sendContents($player);
	}

	public function getEquipment() : EntityEquipment{
		return $this->equipment;
	}

	protected function getDefaultBehaviors() : array{
		return [
		 [
			 // ArrowAttackBehavior
			 new LookAtPlayerBehavior($this, 8),
			 new RandomLookAroundBehavior($this),
			 new WanderBehavior($this)
		 ],
		 [
		  new FloatBehavior($this)
		 ],
		 [
		  new FindAttackableTargetBehavior($this, 8.0),
		  new HurtByTargetBehavior($this, 8.0)
		 ]
		];
	}

	public function getName() : string{
		return "Skeleton";
	}
}
