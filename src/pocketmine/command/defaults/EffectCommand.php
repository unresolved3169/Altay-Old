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

use pocketmine\command\CommandEnumValues;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\utils\TextFormat;

class EffectCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.effect.description",
            "%commands.effect.usage",
            [],
            [
                [
                    new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET, false),
                    new CommandParameter("clear", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("clear", ["clear"]))
                ],
                [
                    new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET, false),
                    new CommandParameter("effect", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getEffect()),
                    new CommandParameter("seconds", CommandParameter::ARG_TYPE_INT),
                    new CommandParameter("amplifier", CommandParameter::ARG_TYPE_INT),
                    new CommandParameter("bool", CommandParameter::ARG_TYPE_VALUE, false, CommandEnumValues::getBoolean())
                ]
            ]
        );
        $this->setPermission("pocketmine.command.effect");
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
            if(($d = $this->getBoundedInt($sender, $args[2], 0, INT32_MAX)) === null){
                return false;
            }
            $duration = $d * 20; //ticks
        }else{
            $duration = null;
        }

        if(count($args) >= 4){
            $amplification = $this->getBoundedInt($sender, $args[3], 0, 255);
            if($amplification === null){
                return false;
            }
        }

        $visible = true;
        if(count($args) >= 5){
            $v = strtolower($args[4]);
            if($v === "on" or $v === "true" or $v === "t" or $v === "1"){
                $visible = false;
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
            $instance = new EffectInstance($effect, $duration, $amplification, $visible);
            $player->addEffect($instance);
            self::broadcastCommandMessage($sender, new TranslationContainer("%commands.effect.success", [$effect->getName(), $instance->getAmplifier(), $player->getDisplayName(), $instance->getDuration() / 20, $effect->getId()]));
        }


        return true;
    }
}
