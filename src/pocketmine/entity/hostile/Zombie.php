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

use pocketmine\entity\Ageable;
use pocketmine\entity\behaviors\BreakDoorBehavior;
use pocketmine\entity\behaviors\FloatBehavior;
use pocketmine\entity\Monster;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class Zombie extends Monster implements Ageable{
    public const NETWORK_ID = self::ZOMBIE;

    public $width = 0.6;
    public $height = 1.8;

    protected function initEntity(){
		parent::initEntity();
		$this->getNavigator()->setBreakDoors(true);
	}

	public function getBehaviors() : array{
		return [
			0 => new FloatBehavior($this),
			1 => new BreakDoorBehavior($this)
		];
	}

	public function getTargetBehaviors() : array{
		return parent::getTargetBehaviors();
	}

	public function getName() : string{
        return "Zombie";
    }

    public function getDrops() : array{
        $drops = [
            ItemFactory::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2))
        ];

        if(mt_rand(0, 199) < 5){
            switch(mt_rand(0, 2)){
                case 0:
                    $drops[] = ItemFactory::get(Item::IRON_INGOT, 0, 1);
                    break;
                case 1:
                    $drops[] = ItemFactory::get(Item::CARROT, 0, 1);
                    break;
                case 2:
                    $drops[] = ItemFactory::get(Item::POTATO, 0, 1);
                    break;
            }
        }

        return $drops;
    }

    public function getXpDropAmount() : int{
        //TODO: check for equipment and whether it's a baby
        return 5;
    }

    public function isBaby() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_BABY);
    }

    public function getSpeed() : float{
        return $this->isBaby() ? 0.35 : 0.23;
    }
}
