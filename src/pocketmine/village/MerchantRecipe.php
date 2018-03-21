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

namespace pocketmine\village;


use pocketmine\item\Item;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;

class MerchantRecipe{

	private $itemToBuy;
	private $secondItemToBuy;
	private $itemToSell;
	private $toolUses;
	private $maxTradeUses;
	private $rewardsExp;

	public function __construct(Item $buy1, Item $buy2, Item $sell, int $toolUsesIn = 0, int $maxTradeUsesIn = 7){
		$this->itemToBuy = $buy1;
		$this->secondItemToBuy = $buy2;
		$this->itemToSell = $sell;
		$this->toolUses = $toolUsesIn;
		$this->maxTradeUses = $maxTradeUsesIn;
		$this->rewardsExp = true;
	}

	public function getItemToBuy() : Item{
		return $this->itemToBuy;
	}

	public function getSecondItemToBuy() : Item{
		return $this->secondItemToBuy;
	}

	public function hasSecondItemToBuy() : bool{
		return !$this->secondItemToBuy->isNull();
	}

	public function getItemToSell() : Item{
		return $this->itemToSell;
	}

	public function getToolUses() : int{
		return $this->toolUses;
	}

	public function getMaxTradeUses() : int{
		return $this->maxTradeUses;
	}

	public function increaseToolUses() : void{
		++$this->toolUses;
	}

	public function increaseMaxTradeUses(int $increment){
		$this->maxTradeUses += $increment;
	}

	public function isRecipeDisabled() : bool{
		return $this->toolUses >= $this->maxToolUses;
	}

	public function getRewardsExp() : bool{
		return $this->rewardsExp;
	}

	public function writeToTags() : CompoundTag{
		$nbt = new CompoundTag("", [
			new ByteTag("rewardExp", 1),
			new IntTag("maxUses", $this->maxTradeUses),
			new IntTag("uses", $this->toolUses),
			$this->itemToBuy->nbtSerialize(-1, "buyA"),
			$this->secondItemToBuy->nbtSerialize(-1, "buyB"),
			$this->itemToSell->nbtSerialize(-1, "sell")
		]);
		return $nbt;
	}
}