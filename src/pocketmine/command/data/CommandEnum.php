<?php

namespace pocketmine\command\data;

class CommandEnum{

    /** @var string */
    public $enumName;
    /** @var string[] */
    public $enumValues = [];

    public function __construct(string $enumName, array $enumValues = []){
        $this->enumName = $enumName;
        $this->enumValues = $enumValues;
    }
}