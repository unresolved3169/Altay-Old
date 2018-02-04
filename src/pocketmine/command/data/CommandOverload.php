<?php

namespace pocketmine\command\data;

class CommandOverload{

    /** @var string */
    protected $name;
    /** @var CommandParameter[] */
    protected $parameters;

    public function __construct(string $name, array $parameters = []){
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function getName(): string{
        return $this->name;
    }

    public function getParameters(): array{
        return $this->parameters;
    }

    public function setParameters(array $parameters): void{
        $this->parameters = $parameters;
    }

    public function setParameter(int $index, CommandParameter $parameter){
        $this->parameters[$index] = $parameter;
    }
}