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
use pocketmine\command\CommandSender;
use pocketmine\command\overload\CommandEnumValues;
use pocketmine\command\overload\CommandOverload;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\JsonNBTParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class GiveCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.give.description",
			"%pocketmine.command.give.usage"
		);
		$this->setPermission("pocketmine.command.give");

		$itemName = new CommandOverload("itemName", [
            CommandParameterUtils::getPlayerParameter(false),
            CommandParameterUtils::getStringEnumParameter("itemName", CommandEnumValues::getItem()),
            CommandParameterUtils::getIntParameter("amount"),
            CommandParameterUtils::getIntParameter("data"),
            CommandParameterUtils::getJsonParameter("components"),
        ]);
		// NOT VANILLA
		$itemId = clone $itemName;
		$itemId->setParameter(1, CommandParameterUtils::getIntParameter("itemId", false));

		$this->setOverloads([
		    $itemName, $itemId
        ]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayer($args[0]);
		$item = ItemFactory::fromString($args[1]);

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
			}catch(\Throwable $ex){
				$exception = $ex;
			}

			if(!($tags instanceof CompoundTag) or $exception !== null){
				$sender->sendMessage(new TranslationContainer("commands.give.tagError", [$exception !== null ? $exception->getMessage() : "Invalid tag conversion"]));
				return true;
			}

			$item->setNamedTag($tags);
		}

		if($player instanceof Player){
			if($item->getId() === 0){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.give.item.notFound", [$args[1]]));

				return true;
			}

			//TODO: overflow
			$player->getInventory()->addItem(clone $item);
		}else{
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

			return true;
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.give.success", [
			$item->getName() . " (" . $item->getId() . ":" . $item->getDamage() . ")",
			(string) $item->getCount(),
			$player->getName()
		]));
		return true;
	}
}
