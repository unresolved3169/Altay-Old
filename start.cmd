@echo off
TITLE Altay server software for Minecraft: Bedrock Edition
cd /d %~dp0

if exist bin\php\php.exe (
	set PHPRC=""
	set PHP_BINARY=bin\php\php.exe
) else (
	set PHP_BINARY=php
)

if exist Altay*.phar (
	set ALTAY_FILE=Altay*.phar
) else (
	if exist Altay.phar (
		set ALTAY_FILE=Altay.phar
	) else (
		if exist PocketMine-MP.phar (
			set ALTAY_FILE=PocketMine-MP.phar
		) else (
		    if exist src\pocketmine\PocketMine.php (
		        set ALTAY_FILE=src\pocketmine\PocketMine.php
			) else (
		        echo "[ERROR] Couldn't find a valid Altay installation."
		        pause
		        exit 8
		    )
	    )
	)
)

if exist bin\mintty.exe (
	start "" bin\mintty.exe -o Columns=88 -o Rows=32 -o AllowBlinking=0 -o FontQuality=3 -o Font="Consolas" -o FontHeight=10 -o CursorType=0 -o CursorBlinks=1 -h error -t "ALTAY_FILE" -w max %PHP_BINARY% %ALTAY_FILE% --enable-ansi %*
) else (
	%PHP_BINARY% -c bin\php %ALTAY_FILE% %*
)