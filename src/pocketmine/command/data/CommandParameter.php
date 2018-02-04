<?php

namespace pocketmine\command\data;

class CommandParameter{

    public const ARG_FLAG_VALID = 0x100000;
    public const ARG_FLAG_ENUM = 0x200000;
    public const ARG_FLAG_POSTFIX = 0x1000000;

    public const ARG_TYPE_INT      = 0x01;
    public const ARG_TYPE_FLOAT    = 0x02;
    public const ARG_TYPE_VALUE    = 0x03;
    public const ARG_TYPE_TARGET   = 0x04;
    public const ARG_TYPE_STRING   = 0x0d;
    public const ARG_TYPE_POSITION = 0x0e;
    public const ARG_TYPE_RAWTEXT  = 0x11;
    public const ARG_TYPE_TEXT     = 0x13;
    public const ARG_TYPE_JSON     = 0x16;
    public const ARG_TYPE_COMMAND  = 0x1d;

    /** @var string */
    public $paramName;
    /** @var int */
    private $paramType;
    /** @var bool */
    public $isOptional;
    /** @var int */
    public $flag;
    /** @var CommandEnum|null */
    public $enum;
    /** @var string|null */
    public $postfix;

    public function __construct(string $paramName, int $paramType, bool $optional, int $flag = self::ARG_FLAG_VALID, CommandEnum $enum = null, string $postfix = null){
        $this->paramName = $paramName;
        $this->paramType = $this->convertParamType($paramType, $flag);
        $this->isOptional = $optional;
        $this->flag = $flag;
        $this->enum = $enum;
        $this->postfix = $postfix ?? "";
    }

    public function convertParamType(int $paramType, int $flag) : int{
        switch($flag){
            case self::ARG_FLAG_ENUM:
            case self::ARG_FLAG_POSTFIX: // guess
                return $flag | self::ARG_FLAG_VALID | $paramType;
            case self::ARG_FLAG_VALID:
                return $flag | $paramType;
        }

        return $paramType;
    }

    public function getParamType(): int{
        return $this->paramType;
    }

    public function setParamType(int $paramType, int $flag = self::ARG_FLAG_VALID): void{
        $this->paramType = $this->convertParamType($paramType, $flag);
    }
}