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

class CommandParameterUtils{

    /** @var CommandParameter */
    protected static $targetParameter;
    /** @var CommandParameter */
    protected static $stringEnumParameter;
    /** @var CommandParameter */
    protected static $intParameter;
    /** @var CommandParameter */
    protected static $valueEnumParameter;
    /** @var CommandParameter */
    protected static $jsonParameter;
    /** @var CommandParameter */
    private static $messageParamater;  // raw text
    /** @var CommandParameter */
    private static $positionParameter;
    /** @var CommandParameter */
    private static $valueParameter;

    public static function init(){
        self::$intParameter = new CommandParameter("int", CommandParameter::ARG_TYPE_INT);
        self::$jsonParameter = new CommandParameter("json", CommandParameter::ARG_TYPE_JSON);
        self::$messageParamater = new CommandParameter("message", CommandParameter::ARG_TYPE_RAWTEXT);
        self::$positionParameter = new CommandParameter("position", CommandParameter::ARG_TYPE_POSITION);
        self::$stringEnumParameter = new CommandParameter("stringenum", CommandParameter::ARG_TYPE_STRING, false, CommandParameter::ARG_FLAG_ENUM);
        self::$targetParameter = new CommandParameter("target", CommandParameter::ARG_TYPE_TARGET);
        self::$valueEnumParameter = new CommandParameter("valueEnum", CommandParameter::ARG_TYPE_VALUE, false, CommandParameter::ARG_FLAG_ENUM);
        self::$valueParameter = new CommandParameter("value", CommandParameter::ARG_TYPE_VALUE);
    }

    public static function getPlayerParameter(bool $optional = true) : CommandParameter{
        return self::getTargetParameter($optional)->setName("player");
    }

    public static function getTargetParameter(bool $optional = true) : CommandParameter{
        return (clone self::$targetParameter)->setOptional($optional);
    }

    public static function getStringEnumParameter(string $paramName, CommandEnum $enum, bool $optional = false) : CommandParameter{
        return (clone self::$stringEnumParameter)->setName($paramName)->setOptional(false)->setEnum($enum);
    }

    public static function getIntParameter(string $paramName, bool $optional = true): CommandParameter{
        return (clone self::$intParameter)->setName($paramName)->setOptional($optional);
    }

    public static function getBoolEnum(bool $optional = true) : CommandParameter{
        return self::getValueEnumParameter($optional, CommandEnumValues::getBoolean());
    }

    public static function getValueParameter(string $paramName, bool $optional = true){
        return (clone self::$valueParameter)->setOptional($optional)->setName($paramName);
    }

    public static function getValueEnumParameter(bool $optional = true, CommandEnum $enum){
        return (clone self::$valueEnumParameter)->setOptional($optional)->setEnum($enum);
    }

    public static function getJsonParameter(string $paramName, bool $optional = true){
        return (clone self::$jsonParameter)->setOptional($optional)->setName($paramName);
    }

    public static function getMessageParameter(bool $optional = true){
        return (clone self::$messageParamater)->setOptional($optional);
    }

    public static function getPositionParameter(string $paramName, bool $optional = true) : CommandParameter{
        return (clone self::$positionParameter)->setOptional($optional)->setName($paramName);
    }
}