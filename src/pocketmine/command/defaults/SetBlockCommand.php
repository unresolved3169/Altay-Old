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
use pocketmine\item\ItemBlock;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SetBlockCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"Changes a block to another block.",
			"/setblock <position: x y z> <tileName: string> [tileData: int] [oldBlockHandling: string]",
            [],
			[
				CommandParameterUtils::getPositionParameter("position", false),
		        CommandParameterUtils::getStringEnumParameter("tileName", new CommandEnum("tileNames", []), false),
		        CommandParameterUtils::getIntParameter("tileData", true),
		        CommandParameterUtils::getStringEnumParameter("oldBlockHandling", new CommandEnum("handling", ["destroy", "keep", "replace"]), true)
			]
		);
		$this->setPermission("pocketmine.command.setblock");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) or !($sender instanceof Player)){
			return true;
		}

		if(count($args) < 4){
			throw new InvalidCommandSyntaxException();
		}

		$pos = null;
		try{
			$x = array_shift($args);
			$y = array_shift($args);
			$z = array_shift($args);

			$pos = new Vector3($x,$y,$z);
		}catch(\Exception $e){
			throw new InvalidCommandSyntaxException();
		}

		$block = ItemBlock::fromString(array_shift($args));

		if($block === null){
			throw new InvalidCommandSyntaxException();
		}

		$block->setDamage($args[0] ?? 0);
		$handling = $args[1] ?? "replace";
		$level = $sender->level;
		$block = $block->getBlock();

		switch ($handling){
			case "destroy":
				$level->useBreakOn($pos);
				$level->setBlock($pos, $block);
				break;
			case "keep":
				if($level->getBlock($pos)->getId() === 0){
					$level->setBlock($pos, $block);
				}
				break;
			case "replace":
			default:
				$level->setBlock($pos, $block);
				break;
		}

		$sender->sendMessage(TextFormat::GREEN . "Block placed successfully.");

		return true;
	}
}
