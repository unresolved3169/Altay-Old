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

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\overload\CommandEnumValues;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ClearCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%altay.command.clear.description",
            "%altay.command.clear.usage",
            [],
            [
                // 3 parameter for Altay (normal 4)
                CommandParameterUtils::getPlayerParameter(),
                CommandParameterUtils::getStringEnumParameter("itemName", CommandEnumValues::getItem()),
                CommandParameterUtils::getIntParameter("maxCount")
            ]
        );

        $this->setPermission("altay.command.clear");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(!($sender instanceof Player) and empty($args)){
            throw new InvalidCommandSyntaxException();
        }

        if(empty($args)){
            if($sender instanceof Player) $player = $sender;
            else{
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
                return true;
            }
        }else{
            $player = $sender->getServer()->getPlayer($args[0]);
            if($player === null){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
                return true;
            }
        }

        if(isset($args[1])){
            $silinen = 0;

            $item = ItemFactory::fromString($args[1]);
            if(isset($args[2])){
                $maxCount = (int) $args[2];
                $silinen = $maxCount;
            }

            if($item->isNull() && isset($maxCount) && $maxCount <= 0){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.clear.failure.no.items"));
                return true;
            }

            $all = $this->getItemCount($item, $player->getInventory());

            if(isset($maxCount)){
                $item->setCount($maxCount);
                $kalan = $player->getInventory()->removeItem($item);

                if(empty($kalan)){
                    $maxCount -= $all;
                    $all = $this->getItemCount($item, $player->getArmorInventory());
                    if($all <= $maxCount){
                        $item->setCount($maxCount);
                        $player->getArmorInventory()->removeItem($item);
                    }
                }

                if($maxCount > 0) $silinen += $maxCount;
            }else{
                $all = $this->getItemCount($item, $player->getInventory());
                $all += $this->getItemCount($item, $player->getArmorInventory());
                $item->setCount($all);
                $player->getInventory()->removeItem($item);
                $player->getArmorInventory()->removeItem($item);

                $silinen = $all;
            }

            $sender->sendMessage(new TranslationContainer("%commands.clear.success", [$player->getName(), $silinen]));

            return true;
        }

        $sayi = count($player->getInventory()->getContents(false));
        $sayi += count($player->getArmorInventory()->getContents(false));
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $sender->sendMessage(new TranslationContainer("%commands.clear.success", [$player->getName(), $sayi]));

        return true;
    }

    public function getItemCount(Item $item, Inventory $inventory) : int{
        $count = 0;
        $checkDamage = !$item->hasAnyDamageValue();
        $checkTags = $item->hasCompoundTag();
        foreach($inventory->getContents(false) as $index => $i){
            if($item->equals($i, $checkDamage, $checkTags)){
                $count += $i->getCount();
            }
        }

        return $count;
    }
}