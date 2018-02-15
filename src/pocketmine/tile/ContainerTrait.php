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

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

/**
 * This trait implements most methods in the {@link Container} interface. It should only be used by Tiles.
 */
trait ContainerTrait{

    abstract public function getNBT() : CompoundTag;

    /**
     * @return Inventory
     */
    abstract public function getRealInventory();

    protected function loadItems() : void{
        if($this->getNBT()->hasTag(Container::TAG_ITEMS, ListTag::class)){
            $inventoryTag = $this->getNBT()->getListTag(Container::TAG_ITEMS);

            $inventory = $this->getRealInventory();
            /** @var CompoundTag $itemNBT */
            foreach($inventoryTag as $itemNBT){
                $inventory->setItem($itemNBT->getByte("Slot"), Item::nbtDeserialize($itemNBT));
            }
        }
    }

    protected function saveItems() : void{
        $items = [];
        foreach($this->getRealInventory()->getContents() as $slot => $item){
            $items[] = $item->nbtSerialize($slot);
        }

        $this->getNBT()->setTag(new ListTag(Container::TAG_ITEMS, $items, NBT::TAG_Compound));
    }
}