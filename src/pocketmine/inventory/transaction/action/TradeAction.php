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

namespace pocketmine\inventory\transaction\action;

use pocketmine\inventory\TradeInventory;
use pocketmine\item\Item;
use pocketmine\Player;

class TradeAction extends InventoryAction{

	/** @var TradeInventory */
	protected $inventory;

	public function __construct(Item $sourceItem, Item $targetItem, ?TradeInventory $inventory){
		parent::__construct($sourceItem, $targetItem);
		$this->inventory = $inventory;
	}

	public function isValid(Player $source) : bool{
		return true;
	}

	public function execute(Player $source) : bool{
		return true;
	}

	public function onPreExecute(Player $source) : bool{
		// TODO : Event
		return true;
	}

	public function onExecuteSuccess(Player $source) : void{
		// TODO : USE++
		// TODO : Willing set ($this->setWilling(mt_rand(1, 3) <= 2);)
	}

	public function onExecuteFail(Player $source) : void{

	}
}