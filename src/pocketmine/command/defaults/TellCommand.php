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
use pocketmine\command\overload\CommandParameter;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TellCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.tell.description",
			"%commands.message.usage",
			["w", "msg"],
			[
				new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET, false),
				new CommandParameter("message", CommandParameter::ARG_TYPE_RAWTEXT, false)
			]
		);
		$this->setPermission("pocketmine.command.tell");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$name = strtolower(array_shift($args));

		$player = $sender->getServer()->getPlayer($name);

		if($player === $sender){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.message.sameTarget"));
			return true;
		}

		if($player instanceof Player){
			$order = implode(" ", $args);
			$gtfo = utf8_encode($order);
			$sender->sendMessage("[{$sender->getName()} -> {$player->getDisplayName()}] " . $gtfo);
			$name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
			$player->sendMessage("[$name -> {$player->getName()}] " . $gtfo);
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
		}

		return true;
	}
}
