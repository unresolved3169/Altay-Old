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

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\Player;

class InventoryClickEvent extends InventoryEvent implements Cancellable{
    public static $handlerList = null;

    /** @var Player */
    protected $who;
    /** @var int */
    protected $inventorySlot;
    /** @var Item */
    protected $item;

    public function __construct(Inventory $inventory, Player $who, int $slot){
        $this->who = $who;
        $this->inventorySlot = $slot;
        $this->item = $inventory->getItem($slot);
        parent::__construct($inventory);
    }

    public function getPlayer() : Player{
        return $this->who;
    }

    public function getItem(): Item{
        return $this->item;
    }

    public function getSlot() : int{
        return $this->inventorySlot;
    }
}