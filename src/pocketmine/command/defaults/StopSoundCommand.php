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
use pocketmine\command\overload\CommandOverload;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;

class StopSoundCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"Stops a sound or all sounds",
			"/stopsound <player: target> [sound: string]",
            []
		);
		$this->setPermission("pocketmine.command.stopsound");

		$sounds = new Config(\pocketmine\RESOURCE_PATH . "sound_definitions.json", Config::JSON, []);
		$soundName = CommandParameterUtils::getStringEnumParameter("soundName", new CommandEnum("sounds", array_keys($sounds->getAll())), true);
		$target = CommandParameterUtils::getPlayerParameter(false);

		$this->setOverloads([
			new CommandOverload("0", [$target]),
			new CommandOverload("1", [$target, $soundName])
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}

		$target = $sender->getServer()->getOfflinePlayer(array_shift($args));

		if(!($target instanceof Player)){
			throw new InvalidCommandSyntaxException();
		}

		$soundName = $args[0] ?? "";
		$stopAll = strlen($soundName) === 0;

		if($target instanceof Player){
			if(!$stopAll) {
				$target->sendMessage(TextFormat::GRAY . "Stopping Sound: " . $soundName);
			}else{
				$target->sendMessage(TextFormat::GRAY . "Stopping All Sounds");
			}
		}

		$pk = new StopSoundPacket();
		$pk->soundName = $soundName;
		$pk->stopAll = $stopAll;

		$target->dataPacket($pk);

		return true;
	}
}
