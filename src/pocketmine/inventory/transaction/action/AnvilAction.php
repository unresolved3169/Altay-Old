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

use pocketmine\inventory\AnvilInventory;
use pocketmine\inventory\transaction\AnvilTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\Player;

class AnvilAction extends SlotChangeAction{

    public function __construct(AnvilInventory $inventory, bool $inputAction, Item $sourceItem, Item $targetItem){
        parent::__construct($inventory, $inputAction ? 0 : 1, $sourceItem, $targetItem);
    }

    public function onAddToTransaction(InventoryTransaction $transaction): void{
        if($this->getSlot() === 0){
            AnvilTransaction::$useInput = $this->getSourceItem();
        }else{
            AnvilTransaction::$useMaterial = $this->getTargetItem();
        }
    }

    public function onExecuteSuccess(Player $source) : void{}

    public function onExecuteFail(Player $source) : void{}
}