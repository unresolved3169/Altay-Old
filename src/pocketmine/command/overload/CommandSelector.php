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

namespace pocketmine\command\overload;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\NoSelectorMatchException;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class CommandSelector{

	public const ESCAPE = "\x40"; // @

	public const ALL_PLAYERS = CommandSelector::ESCAPE . "a";
	public const ALL_ENTITIES = CommandSelector::ESCAPE . "e";
	public const CLOSEST_PLAYER = CommandSelector::ESCAPE . "p";
	public const RANDOM_PLAYER = CommandSelector::ESCAPE . "r";
	public const YOURSELF = CommandSelector::ESCAPE . "s";

	/** @var Entity[] */
	protected $selected = [];

	public function __construct(string $selector, CommandSender $sender, ?array &$args = null, string $entityType = Entity::class){
		if(!self::isSubClass($entityType, Entity::class)){
			throw new NoSelectorMatchException(NoSelectorMatchException::NO_TARGET_MATCH);
		}

		$this->selected = self::setSelectedFromSelector($selector, $entityType, $sender, $args);
		if(empty($this->selected)){
			throw new NoSelectorMatchException((int) self::isSubClass($entityType, Player::class));
		}
	}

	public static function isSubClass(string $sClass, string $sExpectedParentClass) : bool{
		return $sClass === $sExpectedParentClass ? true : is_subclass_of($sClass, $sExpectedParentClass);
	}

	public static function setSelectedFromSelector(string $selector, string $entityType, CommandSender $sender, ?array $args = null) : array{
		switch($selector){
			case CommandSelector::ALL_PLAYERS:
				return $sender->getServer()->getOnlinePlayers();
			case CommandSelector::ALL_ENTITIES:
				$level = self::getPosFromSender($sender)->getLevel();
				return array_filter($level->getEntities(), function($value) use ($entityType) : bool{ return $value instanceof $entityType; });
			case CommandSelector::CLOSEST_PLAYER:
				$pos = self::getPosFromSender($sender);
				return $sender instanceof $entityType ? [$sender] : [$pos->getLevel()->getNearestEntity($pos, 100, $entityType)]; // hmm
			case CommandSelector::RANDOM_PLAYER:
				$players = $sender->getServer()->getOnlinePlayers();
				return [$players[array_rand($players)]];
			case CommandSelector::YOURSELF:
				return $sender instanceof $entityType ? [$sender] : [];
			default: // player name
				$player = $sender->getServer()->getPlayerExact($selector);
				if($player !== null){
					return [$player];
				}elseif($args !== null and !empty($args)){
					$index = array_search($selector, $args);
					$player = self::getPlayerFromArgs($index, $args);
					return $player !== null ? [$player] : [];
				}else{
					return [];
				}
		}
	}

	public static function getPlayerFromArgs(int $startIndex, array &$args) : ?Player{
		$newArgs = array_slice($args, $startIndex, null, true);
		while(!empty($newArgs)){
			$name = implode(" ", $newArgs);
			$player = Server::getInstance()->getPlayerExact($name);
			if($player !== null){
				$lastIndex = $startIndex + count($newArgs);
				$args[$startIndex] = $name;
				for($i=$startIndex + 1; $i<$lastIndex; $i++){
					unset($args[$i]);
				}
				$args = array_values($args);
				return $player;
			}else{
				array_pop($newArgs);
			}
		}

		return null;
	}

	public static function getPosFromSender(CommandSender $sender) : Position{
		return $sender instanceof Position ? $sender : $sender->getServer()->getDefaultLevel()->getSafeSpawn();
	}

	/**
	 * @return Entity[]
	 */
	public function getSelected() : array{
		return $this->selected;
	}

	public function addToSelected(Entity $entity) : void{
		$this->selected[] = $entity;
	}

	public function setSelected(array $selected) : void{
		$this->selected = array_filter($selected, function($value) : bool { return $value instanceof Entity; });
	}

}