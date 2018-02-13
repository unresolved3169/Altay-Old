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
use pocketmine\command\overload\CommandEnum;
use pocketmine\command\overload\CommandEnumValues;
use pocketmine\command\overload\CommandOverload;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;

class TitleCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.title.description",
			"%commands.title.usage"
		);
		$this->setPermission("pocketmine.command.title");

        $playerParameter = CommandParameterUtils::getPlayerParameter(false);

        $this->setOverloads([
            new CommandOverload("clear", [
                $playerParameter,
                CommandParameterUtils::getValueEnumParameter(false, new CommandEnum("clear", ["clear"]))
            ]),
            new CommandOverload("reset", [
                $playerParameter,
                CommandParameterUtils::getValueEnumParameter(false, new CommandEnum("reset", ["reset"]))
            ]),
            new CommandOverload("title", [
                $playerParameter,
                CommandParameterUtils::getStringEnumParameter("TitleSet", CommandEnumValues::getTitleSet()),
                CommandParameterUtils::getMessageParameter(false)->setName("titleText")
            ]),
            new CommandOverload("times", [
                $playerParameter,
                CommandParameterUtils::getValueEnumParameter(false, new CommandEnum("times", ["times"])),
                CommandParameterUtils::getIntParameter("fadeIn"),
                CommandParameterUtils::getIntParameter("stay"),
                CommandParameterUtils::getIntParameter("fadeOut")
            ]),
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
		if($player === null){
			$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
			return true;
		}

		switch($args[1]){
			case "clear":
				$player->removeTitles();
				break;
			case "reset":
				$player->resetTitles();
				break;
			case "title":
				if(count($args) < 3){
					throw new InvalidCommandSyntaxException();
				}

				$player->addTitle(implode(" ", array_slice($args, 2)));
				break;
			case "subtitle":
				if(count($args) < 3){
					throw new InvalidCommandSyntaxException();
				}

				$player->addSubTitle(implode(" ", array_slice($args, 2)));
				break;
			case "actionbar":
				if(count($args) < 3){
					throw new InvalidCommandSyntaxException();
				}

				$player->addActionBarMessage(implode(" ", array_slice($args, 2)));
				break;
			case "times":
				if(count($args) < 4){
					throw new InvalidCommandSyntaxException();
				}

				$player->setTitleDuration($this->getInteger($sender, $args[2]), $this->getInteger($sender, $args[3]), $this->getInteger($sender, $args[4]));
				break;
			default:
				throw new InvalidCommandSyntaxException();
		}

		$sender->sendMessage(new TranslationContainer("commands.title.success"));

		return true;
	}
}
