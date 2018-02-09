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
use pocketmine\command\overload\CommandEnumValues;
use pocketmine\command\overload\CommandOverload;
use pocketmine\command\overload\CommandParameterUtils;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\utils\TextFormat;

class EnchantCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.enchant.description",
			"%commands.enchant.usage"
		);
		$this->setPermission("pocketmine.command.enchant");

		$this->setOverloads([
            new CommandOverload("enchantmentName", [
                CommandParameterUtils::getPlayerParameter(false),
                CommandParameterUtils::getStringEnumParameter("enchantmentName", CommandEnumValues::getEnchant()),
                CommandParameterUtils::getIntParameter("level")
            ]),
		    // NOT VANILLA
		    new CommandOverload("enchantmentId", [
                CommandParameterUtils::getPlayerParameter(false),
		        CommandParameterUtils::getIntParameter("enchantmentId", false),
                CommandParameterUtils::getIntParameter("level")
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
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		$item = $player->getInventory()->getItemInHand();

		if($item->getId() <= 0){
			$sender->sendMessage(new TranslationContainer("commands.enchant.noItem"));
			return true;
		}

		if(is_numeric($args[1])){
			$enchantment = Enchantment::getEnchantment((int) $args[1]);
		}else{
			$enchantment = Enchantment::getEnchantmentByName($args[1]);
		}

		if(!($enchantment instanceof Enchantment)){
			$sender->sendMessage(new TranslationContainer("commands.enchant.notFound", [$args[1]]));
			return true;
		}

		$item->addEnchantment(new EnchantmentInstance($enchantment, (int) ($args[2] ?? 1)));
		$player->getInventory()->setItemInHand($item);


		self::broadcastCommandMessage($sender, new TranslationContainer("%commands.enchant.success", [$player->getName()]));
		return true;
	}
}
