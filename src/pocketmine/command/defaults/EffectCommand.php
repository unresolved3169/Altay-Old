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
use pocketmine\entity\Effect;
use pocketmine\lang\TranslationContainer;
use pocketmine\utils\TextFormat;

class EffectCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.effect.description",
			"%commands.effect.usage"
		);
		$this->setPermission("pocketmine.command.effect");

		$this->setOverloads([
		    new CommandOverload("clear", [
                CommandParameterUtils::getPlayerParameter(false),
		        CommandParameterUtils::getValueEnumParameter(false, new CommandEnum("clear", ["clear"]))
            ]),
            new CommandOverload("effect", [
                CommandParameterUtils::getPlayerParameter(false),
                CommandParameterUtils::getStringEnumParameter("effect", CommandEnumValues::getEffect()),
                CommandParameterUtils::getIntParameter("seconds"),
                CommandParameterUtils::getIntParameter("amplifier"),
                CommandParameterUtils::getBoolEnum()
            ])
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
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		if(strtolower($args[1]) === "clear"){
			foreach($player->getEffects() as $effect){
				$player->removeEffect($effect->getId());
			}

			$sender->sendMessage(new TranslationContainer("commands.effect.success.removed.all", [$player->getDisplayName()]));
			return true;
		}

		$effect = Effect::getEffectByName($args[1]);

		if($effect === null){
			$effect = Effect::getEffect((int) $args[1]);
		}

		if($effect === null){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.effect.notFound", [(string) $args[1]]));
			return true;
		}

		$amplification = 0;

		if(count($args) >= 3){
			$duration = ((int) $args[2]) * 20; //ticks
		}else{
			$duration = $effect->getDefaultDuration();
		}

		if(count($args) >= 4){
			$amplification = (int) $args[3];
			if($amplification > 255){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.num.tooBig", [(string) $args[3], "255"]));
				return true;
			}elseif($amplification < 0){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.num.tooSmall", [(string) $args[3], "0"]));
				return true;
			}
		}

		if(count($args) >= 5){
			$v = strtolower($args[4]);
			if($v === "on" or $v === "true" or $v === "t" or $v === "1"){
				$effect->setVisible(false);
			}
		}

		if($duration === 0){
			if(!$player->hasEffect($effect->getId())){
				if(count($player->getEffects()) === 0){
					$sender->sendMessage(new TranslationContainer("commands.effect.failure.notActive.all", [$player->getDisplayName()]));
				}else{
					$sender->sendMessage(new TranslationContainer("commands.effect.failure.notActive", [$effect->getName(), $player->getDisplayName()]));
				}
				return true;
			}

			$player->removeEffect($effect->getId());
			$sender->sendMessage(new TranslationContainer("commands.effect.success.removed", [$effect->getName(), $player->getDisplayName()]));
		}else{
			$effect->setDuration($duration)->setAmplifier($amplification);

			$player->addEffect($effect);
			self::broadcastCommandMessage($sender, new TranslationContainer("%commands.effect.success", [$effect->getName(), $effect->getAmplifier(), $player->getDisplayName(), $effect->getDuration() / 20, $effect->getId()]));
		}


		return true;
	}
}
