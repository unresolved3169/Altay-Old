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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\Chest;
use pocketmine\block\Hopper;
use pocketmine\inventory\VirtualInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\Player;

class VirtualHolder extends Spawnable implements InventoryHolder, Container, Nameable{
    use NameableTrait, ContainerTrait;

	/** @var VirtualInventory */
	protected $inventory;
	/** @var Block */
	protected $translateBlock;
	/** @var Block */
	protected $holderBlock;
	/** @var int */
	protected $size;
	/** @var int */
	protected $networkType;

	public function __construct(Level $level, CompoundTag $nbt, Block $block){
		parent::__construct($level, $nbt);

		$this->holderBlock = $block;
		$this->validBlock();

		$this->translateBlock = $this->getBlock();
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->loadName($nbt);

		if(!$this->hasName()){
			$this->setName($this->getDefaultName());
		}

		$this->inventory = new VirtualInventory($this);
		$this->loadItems($nbt);
	}

	public function translateBlocks(Player $player){
		$player->level->sendBlocks([$player], [$this->translateBlock]);
	}

	public function spawnTo(Player $player) : bool{
		$pk = new UpdateBlockPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->blockRuntimeId = BlockFactory::toStaticRuntimeId($this->holderBlock->getId(), $this->holderBlock->getDamage());
		$pk->flags = UpdateBlockPacket::FLAG_ALL;
		$player->dataPacket($pk);

		return parent::spawnTo($player);
	}

	public function getRealInventory(){
		return $this->getInventory();
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function getDefaultName(): string{
		return "Altay Virtual Inventory";
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->setTag(new ListTag("Items", [], NBT::TAG_Compound));

		if($item !== null and $item->hasCustomName()){
			$nbt->setString("CustomName", $item->getCustomName());
		}
	}

	public function spawnToAll(){}

	private function validBlock(){
		$validBlocks = [
			Chest::class => [27, WindowTypes::CONTAINER],
			Hopper::class => [5, WindowTypes::HOPPER]
		];

		$class = get_class($this->holderBlock);
		if(isset($validBlocks[$class])){
			$this->size = $validBlocks[$class][0];
			$this->networkType = $validBlocks[$class][1];
		}else{
			throw new \InvalidArgumentException("$class is not valid block for virtual inventories.");
		}
	}

	public function getSize() : int{
		return $this->size;
	}

	public function getNetworkType() : int{
		return $this->networkType;
	}
}