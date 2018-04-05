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

use pocketmine\command\Command;

class CommandData{

    /** @var string */
    public $commandName;
    /** @var string */
    public $commandDescription;
    /** @var int */
    public $flags; // TODO
    /** @var int */
    public $permission; // TODO : Add permission level
    /** @var array */
    public $aliases;
    /** @var CommandOverload[] */
    public $overloads = [];

    public function __construct(Command $command = null, int $flags = 0){
        if($command != null){
            $this->commandName = $command->getName();
            $this->commandDescription = $command->getDescription();
            $this->flags = $flags;
            $this->permission = 0;
            $this->aliases = $command->getAliases();
            $this->overloads = $command->getOverloads();
        }
    }
}