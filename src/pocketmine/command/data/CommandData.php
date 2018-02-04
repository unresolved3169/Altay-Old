<?php

namespace pocketmine\command\data;

use pocketmine\command\Command;

class CommandData{

    /** @var string */
    public $commandName;
    /** @var string */
    public $commandDescription;
    /** @var int */
    public $flags;
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