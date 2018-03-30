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
use pocketmine\command\overload\CommandEnum;
use pocketmine\command\overload\CommandOverload;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TeleportCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.tp.description",
			"%commands.tp.usage",
            ["teleport"]
		);
		$this->setPermission("pocketmine.command.teleport");

		// TODO : Vanilla ile uyumlu olsun

		$destination = CommandParameterUtils::getPositionParameter("destination", false);
		$facing = CommandParameterUtils::getValueEnumParameter(false, new CommandEnum("facing", ["facing"]));
		$lookAtEntity = CommandParameterUtils::getTargetParameter(false)->setName("lookAtEntity");
		$yRot = CommandParameterUtils::getValueParameter("yRot");

		$this->setOverloads([
		    new CommandOverload("1", [
		        $destination,
                $yRot,
                (clone $yRot)->setName("xRot")
            ]),
		    new CommandOverload("2", [
		        $destination,
                $facing,
                (clone $destination)->setName("lookAtPosition")
            ]),
            new CommandOverload("3", [
		        $destination,
                $facing,
                $lookAtEntity
            ]),
		    new CommandOverload("4", [
                (clone $lookAtEntity)->setName("victim"),
                $destination,
                $yRot,
                $yRot->setName("xRot")
            ]),
		    new CommandOverload("5", [
                (clone $lookAtEntity)->setName("victim"),
                $destination,
                $facing,
                (clone $destination)->setName("lookAtPosition")
            ]),
		    new CommandOverload("6", [
                (clone $lookAtEntity)->setName("victim"),
                $destination,
                $facing,
                $lookAtEntity
            ]),
		    new CommandOverload("7", [
                (clone $lookAtEntity)->setName("destination")
            ]),
		    new CommandOverload("8", [
                (clone $lookAtEntity)->setName("victim"),
                (clone $lookAtEntity)->setName("destination")
            ])
        ]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$args = array_values(array_filter($args, function($arg){
			return strlen($arg) > 0;
		}));
		if(count($args) < 1 or count($args) > 6){
			throw new InvalidCommandSyntaxException();
		}

		$target = null;
		$origin = $sender;

		if(count($args) === 1 or count($args) === 3){
			if($sender instanceof Player){
				$target = $sender;
			}else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
			if(count($args) === 1){
				$target = $sender->getServer()->getPlayer($args[0]);
				if($target === null){
					$sender->sendMessage(TextFormat::RED . "Can't find player " . $args[0]);

					return true;
				}
			}
		}else{
			$target = $sender->getServer()->getPlayer($args[0]);
			if($target === null){
				$sender->sendMessage(TextFormat::RED . "Can't find player " . $args[0]);

				return true;
			}
			if(count($args) === 2){
				$origin = $target;
				$target = $sender->getServer()->getPlayer($args[1]);
				if($target === null){
					$sender->sendMessage(TextFormat::RED . "Can't find player " . $args[1]);

					return true;
				}
			}
		}

		if(count($args) < 3){
			$origin->teleport($target);
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.tp.success", [$origin->getName(), $target->getName()]));

			return true;
		}elseif($target->getLevel() !== null){
			if(count($args) === 4 or count($args) === 6){
				$pos = 1;
			}else{
				$pos = 0;
			}

			$x = $this->getRelativeDouble($target->x, $sender, $args[$pos++]);
			$y = $this->getRelativeDouble($target->y, $sender, $args[$pos++], 0, 256);
			$z = $this->getRelativeDouble($target->z, $sender, $args[$pos++]);
			$yaw = $target->getYaw();
			$pitch = $target->getPitch();

			if(count($args) === 6 or (count($args) === 5 and $pos === 3)){
				$yaw = (float) $args[$pos++];
				$pitch = (float) $args[$pos++];
			}

			$target->teleport(new Vector3($x, $y, $z), $yaw, $pitch);
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.tp.success.coordinates", [$target->getName(), round($x, 2), round($y, 2), round($z, 2)]));

			return true;
		}

		throw new InvalidCommandSyntaxException();
	}
}
