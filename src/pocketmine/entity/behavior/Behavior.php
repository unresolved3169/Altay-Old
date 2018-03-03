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

namespace pocketmine\entity\behavior;

use pocketmine\entity\Living;

abstract class Behavior{

    /** @var Living */
	protected $mob;
	
	public function getName() : string{
		return (new \ReflectionClass($this))->getShortName();
	}
	
	public function __construct(Living $mob){
		$this->mob = $mob;
	}
	
	public abstract function canStart() : bool;
	
	public function onStart() : void{}
	
	public function canContinue() : bool{
		return $this->canStart();
	}
	
	public function onTick(int $tick) : void{}
	
	public function onEnd() : void{}
	
}