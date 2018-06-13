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

use pocketmine\inventory\ShulkerBoxInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\nbt\tag\CompoundTag;

class ShulkerBox extends Spawnable implements InventoryHolder, Container, Nameable {
	use NameableTrait, ContainerTrait;

	/** @var ShulkerBoxInventory */
	protected $inventory;

	/**
	 * @return int
	 */
	public function getSize(){
		return 27;
	}

	public function getDefaultName(): string{
		return "Shulker Box";
	}

	/**
	 * Get the object related inventory
	 *
	 * @return Inventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function getRealInventory(){
		return $this->inventory;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadName($nbt);

		$this->inventory = new ShulkerBoxInventory($this);
		$this->loadItems($nbt);
	}

	public function writeSaveData(CompoundTag $nbt) : void{
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}
}
