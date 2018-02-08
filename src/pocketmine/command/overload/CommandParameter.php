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
    public $paramType;
    /** @var bool */
    public $isOptional;
    /** @var int */
    public $flag;
    /** @var CommandEnum|null */
    public $enum;
    /** @var string|null */
    public $postfix;

    public function __construct(string $paramName, int $paramType, bool $optional = true, int $flag = self::ARG_FLAG_VALID, CommandEnum $enum = null, string $postfix = null){
        $this->paramName = $paramName;
        $this->paramType = $paramType;
        $this->isOptional = $optional;
        $this->flag = $flag;
        $this->enum = $enum;
        $this->postfix = $postfix ?? "";
    }

    public function setName(string $paramName) : CommandParameter{
        $this->paramName = $paramName;

        return $this;
    }

    public function setType(int $paramType) : CommandParameter{
        $this->paramType = $paramType;

        return $this;
    }

    public function setEnum(?CommandEnum $enum) : CommandParameter{
        $this->enum = $enum;

        return $this;
    }
}