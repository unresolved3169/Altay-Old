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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\level\Position;

class RedstoneUtils{

	/** @var Block[][] */
	protected static $takePowers = [];

	public static function checkPower(Position $pos) : bool{
		if(isset(self::$takePowers[$pos->getLevel()->getFolderName()][Level::blockHash($pos->x, $pos->y, $pos->z)])){
			$source = self::$takePowers[$pos->getLevel()->getFolderName()][Level::blockHash($pos->x, $pos->y, $pos->z)];
			if($source->isRedstoneSource()){
				return true;
			}else{
				self::removeFromTakePowers($pos);
			}
		}

		return false;
	}

	public static function updateTakePowers(Block $source, Position $pos){
		if($source->isRedstoneSource()){
			self::addToTakePowers($source, $pos);
		}else{
			self::removeFromTakePowers($pos);
		}
	}

	protected static function addToTakePowers(Block $source, Position $pos){
		self::$takePowers[$pos->getLevel()->getFolderName()][Level::blockHash($pos->x, $pos->y, $pos->z)] = $source;
	}

	protected static function removeFromTakePowers(Position $pos){
		unset(self::$takePowers[$pos->getLevel()->getFolderName()][Level::blockHash($pos->x, $pos->y, $pos->z)]);
	}
}