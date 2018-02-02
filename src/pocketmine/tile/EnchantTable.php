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

namespace pocketmine\tile;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class EnchantTable extends Spawnable implements Nameable{
	use NameableTrait;

	public function getDefaultName() : string{
		return "Enchanting Table";
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->hasName()){
			$nbt->setTag($this->namedtag->getTag("CustomName"));
		}
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		if($item !== null and $item->hasCustomName()){
			$nbt->setString("CustomName", $item->getCustomName());
		}
	}
}
