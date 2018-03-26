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

namespace pocketmine\entity\behaviors;

use pocketmine\entity\Mob;

class EntityJumpHelper{

    /** @var Mob */
    protected $entity;
    /** @var bool */
    protected $isJumping = false;

    public function __construct(Mob $mob){
        $this->entity = $mob;
    }

    public function setJumping() : void{
        $this->isJumping = true;
    }

    public function doJump(){
        $this->entity->setJumping($this->isJumping);
        $this->isJumping = false;
    }

}