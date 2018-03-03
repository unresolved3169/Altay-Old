<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\item\Item as ItemItem;
use pocketmine\item\ItemFactory;
use pocketmine\entity\behavior\{WanderBehavior, RandomLookAroundBehavior, LookAtPlayerBehavior, FindAttackableTargetBehavior};

class Zombie extends Monster{
	public const NETWORK_ID = self::ZOMBIE;

	public $width = 0.6;
	public $height = 1.8;

	public function getName() : string{
		return "Zombie";
	}

	public function getDrops() : array{
		$drops = [
			ItemFactory::get(ItemItem::ROTTEN_FLESH, 0, mt_rand(0, 2))
		];

		if(mt_rand(0, 199) < 5){
			switch(mt_rand(0, 2)){
				case 0:
					$drops[] = ItemFactory::get(ItemItem::IRON_INGOT, 0, 1);
					break;
				case 1:
					$drops[] = ItemFactory::get(ItemItem::CARROT, 0, 1);
					break;
				case 2:
					$drops[] = ItemFactory::get(ItemItem::POTATO, 0, 1);
					break;
			}
		}

		return $drops;
	}

	public function getXpDropAmount() : int{
		//TODO: check for equipment and whether it's a baby
		return 5;
	}
	
	protected function addBehaviors(){
		$this->behaviorManager->addBehavior(new FindAttackableTargetBehavior($this, 35));
		$this->behaviorManager->addBehavior(new WanderBehavior($this, 1.0));
		$this->behaviorManager->addBehavior(new LookAtPlayerBehavior($this, 8.0));
		$this->behaviorManager->addBehavior(new RandomLookAroundBehavior($this));
	}
}
