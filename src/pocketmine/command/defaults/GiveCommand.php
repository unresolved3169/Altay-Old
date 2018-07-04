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

use pocketmine\command\Command;
use pocketmine\command\CommandEnumValues;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\utils\TextFormat;

class GiveCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.give.description",
            "%pocketmine.command.give.usage"
        );
        $this->setPermission("pocketmine.command.give");

        $itemName = [
            new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET, false),
            new CommandParameter("itemName", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getItem()),
            new CommandParameter("amount", CommandParameter::ARG_TYPE_INT),
            //new CommandParameter("data", CommandParameter::ARG_TYPE_INT), not in Altay
            new CommandParameter("components", CommandParameter::ARG_TYPE_JSON),
        ];
        // FOR ALTAY NOT IN VANILLA
        $itemId = $itemName;
        $itemId[1] = new CommandParameter("itemId", CommandParameter::ARG_TYPE_INT, false);

        $this->setParameters($itemName, 0);
        $this->setParameters($itemId, 1);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) < 2){
            throw new InvalidCommandSyntaxException();
        }

        $player = $sender->getServer()->getPlayer($args[0]);
        if($player === null){
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
            return true;
        }

        try{
            $item = ItemFactory::fromString($args[1]);
        }catch(\InvalidArgumentException $e){
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.give.item.notFound", [$args[1]]));
            return true;
        }

        if(!isset($args[2])){
            $item->setCount($item->getMaxStackSize());
        }else{
            $item->setCount((int) $args[2]);
        }

        if(isset($args[3])){
            $tags = $exception = null;
            $data = implode(" ", array_slice($args, 3));
            try{
                $tags = JsonNBTParser::parseJSON($data);
            }catch(\Exception $ex){
                $exception = $ex;
            }

            if(!($tags instanceof CompoundTag) or $exception !== null){
                $sender->sendMessage(new TranslationContainer("commands.give.tagError", [$exception !== null ? $exception->getMessage() : "Invalid tag conversion"]));
                return true;
            }

            $item->setNamedTag($tags);
        }

        //TODO: overflow
        $player->getInventory()->addItem(clone $item);

        Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.give.success", [
            $item->getName() . " (" . $item->getId() . ":" . $item->getDamage() . ")",
            (string) $item->getCount(),
            $player->getName()
        ]));
        return true;
    }
}
