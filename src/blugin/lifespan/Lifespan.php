<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\lifespan;

use blugin\lib\command\BaseCommandTrait;
use blugin\lib\command\listener\AvaliableCommandListener;
use blugin\lib\translator\traits\TranslatorHolderTrait;
use blugin\lib\translator\TranslatorHolder;
use blugin\lifespan\command\overload\ArrowLifespanOverload;
use blugin\lifespan\command\overload\ItemLifespanOverload;
use blugin\traits\singleton\SingletonTrait;
use blugin\utils\arrays\ArrayUtil as Arr;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Lifespan extends PluginBase implements Listener, TranslatorHolder{
    use TranslatorHolderTrait, BaseCommandTrait, SingletonTrait;

    public const ITEM = "Item";
    public const ARROW = "Arrow";

    public const DEFAULTS = [
        self::ITEM => 6000,
        self::ARROW => 1200
    ];

    /** @var int[] (short) */
    private $lifespanMap;

    public function onLoad() : void{
        self::$instance = $this;

        $this->loadLanguage();
        $this->getBaseCommand();
    }

    public function onEnable() : void{
        //Register main command with subcommands
        $command = $this->getBaseCommand();
        $command->addOverload(new ItemLifespanOverload($command));
        $command->addOverload(new ArrowLifespanOverload($command));
        $this->getServer()->getCommandMap()->register($this->getName(), $command);

        //Load lifespan data
        $dataPath = "{$this->getDataFolder()}lifespan.json";
        if(!file_exists($dataPath)){
            $this->lifespanMap = self::DEFAULTS;
            return;
        }

        $content = file_get_contents($dataPath);
        if($content === false)
            throw new \RuntimeException("Unable to load lifespan.json file");

        $data = json_decode($content, true);
        if(!is_array($data) || Arr::validate(self::DEFAULTS, function(string $tag) use ($data){ return !is_numeric($data[$tag] ?? null); })){
            throw new \RuntimeException("Invalid data in lifespan.json file. Must be int array");
        }
        $this->lifespanMap = $data;

        //Register event listeners
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        AvaliableCommandListener::register($this);
    }

    public function onDisable() : void{
        //Unregister main command with subcommands
        $this->getServer()->getCommandMap()->unregister($this->getBaseCommand());

        //Save lifespan data
        if(is_array($this->lifespanMap)){
            file_put_contents("{$this->getDataFolder()}lifespan.json", json_encode($this->lifespanMap, JSON_PRETTY_PRINT));
        }
    }

    /**
     * @priority MONITOR
     */
    public function onEntitySpawnEvent(EntitySpawnEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof ItemEntity){
            static $itemLifeProperty = null;
            if($itemLifeProperty === null){
                $itemReflection = new \ReflectionClass(ItemEntity::class);
                $itemLifeProperty = $itemReflection->getProperty("age");
                $itemLifeProperty->setAccessible(true);
            }

            $before = $itemLifeProperty->getValue($entity);
            $itemLifeProperty->setValue($entity, min(0x7fff, max(0, $before + 6000 - $this->getLifespan(self::ITEM))));
        }elseif($entity instanceof Arrow){
            static $arrowLifeProperty = null;
            if($arrowLifeProperty === null){
                $arrowReflection = new \ReflectionClass(Arrow::class);
                $arrowLifeProperty = $arrowReflection->getProperty("collideTicks");
                $arrowLifeProperty->setAccessible(true);
            }

            $before = $arrowLifeProperty->getValue($entity);
            $arrowLifeProperty->setValue($entity, min(0x7fff, max(0, $before + 1200 - $this->getLifespan(self::ARROW))));
        }
    }

    public function getLifespan(string $mode) : ?int{
        return $this->lifespanMap[$mode] ?? null;
    }

    public function setLifespan(string $mode, int $value) : void{
        if(!isset(self::DEFAULTS[$mode]))
            throw new \InvalidArgumentException("Mode '{$mode}' is invalid");

        if($value < 0)
            throw new \InvalidArgumentException("Value {$value} is too small, it must be at least 0");

        if($value > 0x7fff)
            throw new \InvalidArgumentException("Value {$value} is too big, it must be at most 0x7fff");

        $this->lifespanMap[$mode] = $value;
    }
}
