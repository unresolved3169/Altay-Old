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
use pocketmine\entity\behavior\TemptedBehavior;
use pocketmine\entity\behavior\WanderBehavior;
use pocketmine\entity\behavior\FloatBehavior;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class Rabbit extends Animal{
	public const NETWORK_ID = self::RABBIT;

	public $width = 0.67;
	public $height = 0.67;

	public const COAT_BROWN = 0;
	public const COAT_WHITE = 1;
	public const COAT_BLACK = 2;
	public const COAT_SPLOTCHED = 3;
	public const COAT_DESERT = 4;
	public const COAT_SALT = 5;

	protected function initEntity(){
		$this->setHealth(3);

		parent::initEntity();

		/** @var int $coat */
		$coat = $this->namedtag->getInt("Variant", mt_rand(0, 5));

		if($coat > 5 or $coat < 0){
			$coat = mt_rand(0, 5);
		}

		$this->setCoat($coat);
	}

	public function getName(): string{
		return "Rabbit";
	}

	protected function getDefaultBehaviors(): array{
		return [
		 [
			 new PanicBehavior($this, 60, $this->getMovementSpeed(), 2.2),
			 new TemptedBehavior($this, [
				 Item::CARROT,
				 Item::GOLDEN_CARROT,
				 Item::YELLOW_FLOWER
			 ], 8.0, 1.0),
			 new WanderBehavior($this, 0.6),
			 new LookAtPlayerBehavior($this)
			],
			[
			 new FloatBehavior($this)
			]
		];
	}

	public function setCoat(int $coat){
		$this->propertyManager->setInt(self::DATA_VARIANT, $coat);
	}

	public function getCoat() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function getXpDropAmount(): int{
		return $this->isBaby() ? 0 : mt_rand(1, 3);
	}

	public function getDrops() : array{
		$drops = [ItemFactory::get(Item::RABBIT_HIDE, 0, mt_rand(0, 1))];

		$id = $this->getLastDamageCause()->getCause() == EntityDamageEvent::CAUSE_FIRE ? Item::COOKED_RABBIT : Item::RAW_RABBIT;
		$drops[] = ItemFactory::get($id, 0, mt_rand(0, 1));

		if(mt_rand(0, 99) < 10){
			$drops[] = ItemFactory::get(Item::RABBIT_FOOT);
		}

		return $drops;
	}

	public function getAdditionalJumpVelocity() : float{
		return 0.03; // TODO : Find for rabbit
	}
}