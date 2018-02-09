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
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

// TODO : Add Language and postfixes
class XpCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "Oyuncu deneyimi ekler veya kaldırır",
            '/xp <değer> [oyuncu]',
            [],
            [
                CommandParameterUtils::getIntParameter("amount", false),
                CommandParameterUtils::getPlayerParameter()
            ]
        );

        $this->setPermission("pocketmine.command.xp");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)) return true;

        if(count($args) < 1){
            throw new InvalidCommandSyntaxException();
        }

        if(count($args) < 2){
            if($sender instanceof ConsoleCommandSender){
                $sender->sendMessage(TextFormat::RED . "Bu komut sadece oyun içi çalışır.");
                return true;
            }
            $player = $sender;
        }else{
            $player = $sender->getServer()->getPlayer($args[1]);
        }

        $xp = $args[0];

        if($player instanceof Player){
            $isim = $player->getName();
            if(strcasecmp(substr($xp, -1), "L") == 0){ // Level var
                $xp = (int) rtrim($xp, "Ll");
                if($xp > 0){
                    $player->addXpLevels($xp);
                    $sender->sendMessage(new TranslationContainer("%commands.xp.success.levels", [$xp, $isim]));
                    return true;
                }elseif($xp < 0){
                    $xp = abs($xp);
                    $player->subtractXpLevels($xp);
                    $sender->sendMessage(new TranslationContainer("%commands.xp.success.negative.levels", [$xp, $isim]));
                    return true;
                }
            }else{
                $xp = (int) $xp;
                if($xp > 0){
                    $player->addXp($xp);
                    $sender->sendMessage(new TranslationContainer("%commands.xp.success", [$xp, $isim]));
                    return true;
                }elseif($xp < 0){
                    $sender->sendMessage(new TranslationContainer("%commands.xp.failure.withdrawXp"));
                    return true;
                }
            }
        }else{
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
            return false;
        }

        throw new InvalidCommandSyntaxException();
    }
}