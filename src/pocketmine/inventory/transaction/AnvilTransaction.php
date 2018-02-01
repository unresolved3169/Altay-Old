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

namespace pocketmine\inventory\transaction;

use pocketmine\inventory\AnvilInventory;
use pocketmine\inventory\transaction\action\AnvilResultAction;
use pocketmine\item\EnchantedBook;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;

class AnvilTransaction extends InventoryTransaction{

    /** @var Item */
    protected $result = null;
    /** @var Item */
    public static $useInput = null;
    /** @var Item */
    public static $useMaterial = null;

    /** @var AnvilInventory */
    private $inventory;

    public function __construct(Player $source, $actions = []){
        $this->creationTime = microtime(true);
        $this->source = $source;

        foreach($actions as $action){
            if($action instanceof AnvilResultAction){
                if($this->result == null){
                    $this->result = $action->getSourceItem();
                    $this->inventory = $action->getInventory();
                }
            }
            $this->addAction($action);
        }
    }

    public function execute() : bool{
        if($this->hasExecuted() or !$this->canExecute()){
            $this->sendInventories();
            return false;
        }

        if(!$this->callExecuteEvent()){
            $this->sendInventories();
            return false;
        }

        foreach($this->actions as $action){
            if(!$action->onPreExecute($this->source)){
                $this->sendInventories();
                return false;
            }
        }

        foreach($this->actions as $action){
            if($action->execute($this->source)){
                $action->onExecuteSuccess($this->source);
            }else{
                $action->onExecuteFail($this->source);
            }
        }

        $this->inventory->setItem(0, Item::get(0), false);
        if(self::$useMaterial != null){
            $item = $this->inventory->getItem(1);
            if($item->getCount() - self::$useMaterial->getCount() < 1){
                $item = Item::get(0);
            }else{
                $item = $item->setCount($item->getCount() - self::$useMaterial->getCount());
            }
            $this->inventory->setItem(1, $item, false);
        }

        $cost = self::$useInput->getRepairCost();
        if(self::$useInput->getCustomName() !== $this->result->getCustomName()){
            $cost++;
        }
        if(self::$useMaterial != null){
            $cost += self::$useMaterial->getRepairCost();
            if (self::$useMaterial instanceof EnchantedBook) {
                foreach ($this->result->getEnchantments() as $enchant) {
                    $inputEnchant = self::$useInput->getEnchantment($enchant->getId());
                    if ($inputEnchant == null) {
                        $cost += $enchant->getRepairCost() / 2;
                    } else if ($enchant->getLevel() != $inputEnchant->getLevel()) {
                        $check = Enchantment::getEnchantment($enchant->getId());
                        $check = new EnchantmentInstance($check, $enchant->getLevel() - $inputEnchant->getLevel());
                        $cost += $check->getRepairCost() / 2; // TODO : Fix repair cost
                    }
                }
            }elseif(self::$useMaterial->isTool()){
                $ench = 0;
                foreach($this->result->getEnchantments() as $enchant){
                    $ench += $enchant->getRepairCost();
                }
                foreach(self::$useInput->getEnchantments() as $enchant){
                    $ench -= $enchant->getRepairCost();
                }
                $cost += $ench;
            }else{
                $cost += self::$useMaterial->getCount();
            }
        }
        $this->source->setXpLevel($this->source->getXpLevel() - $cost);

        $this->hasExecuted = true;

        return true;
    }
}