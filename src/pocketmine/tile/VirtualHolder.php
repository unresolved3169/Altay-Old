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
use pocketmine\inventory\VirtualInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
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

    public function __construct(Level $level, CompoundTag $nbt, Block $block){
        parent::__construct($level, $nbt);

        if(!$this->hasName()) $this->setName($this->getDefaultName());

        $this->inventory = new VirtualInventory($this);
        $this->loadItems();

        $this->translateBlock = $this->getBlock();
        $this->holderBlock = $block;
    }

    public function translateBlocks(Player $player){
        $player->level->sendBlocks([$player], [$this->translateBlock]);
    }

    public function spawnTo(Player $player) : bool{
        $pk = new UpdateBlockPacket();
        $pk->x = $this->x;
        $pk->y = $this->y;
        $pk->z = $this->z;
        $pk->blockId = $this->holderBlock->getId();
        $pk->blockData = $this->holderBlock->getDamage();
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

    public function addAdditionalSpawnData(CompoundTag $nbt): void{
        if($this->hasName()){
            $nbt->setTag($this->namedtag->getTag("CustomName"));
        }
    }

    public function saveNBT() : void{
        parent::saveNBT();
        $this->saveItems();
    }

    protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
        $nbt->setTag(new ListTag("Items", [], NBT::TAG_Compound));

        if($item !== null and $item->hasCustomName()){
            $nbt->setString("CustomName", $item->getCustomName());
        }
    }

    public function spawnToAll(){}
}