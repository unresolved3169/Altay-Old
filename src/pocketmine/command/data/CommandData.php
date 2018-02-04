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
    /** @var CommandEnum|null */
    public $aliases;
    /** @var CommandParameter[][] */
    public $overloads = [];

    public function __construct(Command $command = null, int $flags = 0){
        if($command != null){
            $this->commandName = $command->getName();
            $this->commandDescription = $command->getDescription();
            $this->flags = $flags;
            $this->permission = 0;
            if(!empty($command->getAliases())){
                $this->aliases = new CommandEnum($command->getName()."CommandAliases", $command->getAliases());
            }
            $this->overloads = $this->convertOverload($command->getOverloads());
        }
    }

    /**
     * @param CommandOverload[] $overloads
     * @return array
     */
    public function convertOverload(array $overloads) : array{
        $array = [];

        /** @var CommandOverload[] $overloads */
        $overloads = array_values($overloads);
        foreach($overloads as $index => $overload){
            $parameters = array_values($overload->getParameters());
            $array[$index] = $parameters;
        }

        return $array;
    }
}