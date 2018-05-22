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

namespace pocketmine\entity\passive;

use pocketmine\entity\Animal;
use pocketmine\entity\behavior\LookAtPlayerBehavior;
use pocketmine\entity\behavior\PanicBehavior;
use pocketmine\entity\behavior\RandomLookAroundBehavior;
use pocketmine\entity\behavior\TemptedBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\entity\Rideable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

// TODO : Saddle
class Pig extends Animal implements Rideable{
	public const NETWORK_ID = self::PIG;

	public $width = 0.9;
	public $height = 0.9;

	protected function initEntity(){
		$this->setMaxHealth(10);
		parent::initEntity();
	}

	public function getName() : string{
		return "Pig";
	}

	public function getDefaultBehaviors(): array{
		return [
			[
			 new PanicBehavior($this, 60, 0.25, 1.25),
			 new TemptedBehavior($this, [
				 Item::POTATO,
				 Item::CARROT,
				 Item::BEETROOT,
				 Item::CARROT_ON_A_STICK
			 ], 10, 1.2),
			 new RandomLookAroundBehavior($this),
			 new LookAtPlayerBehavior($this),
			 new WanderBehavior($this)
			],
			[
			 new FloatBehavior($this)
			]
		];
	}

	public function getXpDropAmount() : int{
		return mt_rand(1,3);
	}

	public function getDrops() : array{
		$id = $this->getLastDamageCause()->getCause() == EntityDamageEvent::CAUSE_FIRE ? Item::COOKED_PORKCHOP : Item::RAW_PORKCHOP;
		return [
			ItemFactory::get($id, 0, mt_rand(1, 3))
		];
	}
}