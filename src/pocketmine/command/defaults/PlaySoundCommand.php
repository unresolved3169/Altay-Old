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
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class PlaySoundCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"Plays a sound",
			"/playsound <sound: string> <player: target> [position: x y z] [volume: float] [pitch: float]",
            [],
            [CommandParameterUtils::getPlayerParameter(false)]
		);
		$this->setPermission("pocketmine.command.playsound");

		// TODO: Add parameters
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$soundName = array_shift($args);
		$target = $sender->getServer()->getOfflinePlayer(array_shift($args));

		if(!($target instanceof Player)){
			throw new InvalidCommandSyntaxException();
		}

		if(isset($args[0]) and count($args) >= 3){
			$x = array_shift($args);
			$y = array_shift($args);
			$z = array_shift($args);

			$pos = new Vector3($x,$y,$z);
		}else{
			$pos = $target->asVector3();
		}

		if($target instanceof Player){
			$target->sendMessage(TextFormat::GRAY . "Playing Sound: " . $soundName);
		}

		$pk = new PlaySoundPacket();
		$pk->soundName = $soundName;
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->volume = $args[0] ?? 1.0;
		$pk->pitch = $args[1] ?? 1.0;

		$target->dataPacket($pk);

		return true;
	}
}
