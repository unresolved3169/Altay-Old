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

namespace pocketmine\entity\boss;

use pocketmine\entity\Animal;
use pocketmine\entity\Entity;
use pocketmine\item\Item;

class ElderGuardian extends Animal {

	const NETWORK_ID = self::ELDER_GUARDIAN;

	public $width = 1.45;
	public $height = 0;
	
	public $drag = 0.2;
	public $gravity = 0.3;

	/**
	 * @return string
	 */
	public function getName() : string{
		return "Elder Guardian";
	}

	protected function initEntity(){
		$this->setMaxHealth(80);
		$this->setGenericFlag(Entity::DATA_FLAG_ELDER, true);
		parent::initEntity();
	}

    /**
     * @return Item[]
     */
    public function getDrops() : array{
		$drops = [
			Item::get(Item::PRISMARINE_CRYSTALS, 0, mt_rand(0, 1))
		];
		$drops[] = Item::get(Item::PRISMARINE_SHARD, 0, mt_rand(0, 2));

		return $drops;
	}

	public function getXpDropAmount(): int{
        return 10;
    }
}
