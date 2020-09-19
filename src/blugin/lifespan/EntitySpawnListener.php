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

use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;

class EntitySpawnListener implements Listener{
    public const CLASS_MAP = [
        ItemEntity::class => Lifespan::ITEM,
        Arrow::class => Lifespan::ARROW,
    ];

    /** @var \ReflectionProperty[] string (mode) => reflection property */
    private static $properties = [];

    private function __construct(){
        if(!isset(self::$properties[Lifespan::ITEM])){
            $itemReflection = new \ReflectionClass(ItemEntity::class);
            $itemLifeProperty = $itemReflection->getProperty("age");
            $itemLifeProperty->setAccessible(true);
            self::$properties[Lifespan::ITEM] = $itemLifeProperty;
        }
        if(!isset(self::$properties[Lifespan::ARROW])){
            $arrowReflection = new \ReflectionClass(Arrow::class);
            $arrowLifeProperty = $arrowReflection->getProperty("collideTicks");
            $arrowLifeProperty->setAccessible(true);
            self::$properties[Lifespan::ARROW] = $arrowLifeProperty;
        }
    }

    /** @priority MONITOR */
    public function onEntitySpawnEvent(EntitySpawnEvent $event) : void{
        $entity = $event->getEntity();
        foreach(self::CLASS_MAP as $class => $mode){
            if(!$entity instanceof $class)
                continue;

            if(self::$properties[$mode]->getValue($entity) > 0)
                return;

            self::$properties[$mode]->setValue($entity, Lifespan::DEFAULTS[$mode] - Lifespan::getInstance()->getLifespan(Lifespan::ITEM));
        }
    }

    public static function register(Plugin $plugin) : void{
        $plugin->getServer()->getPluginManager()->registerEvents(new self(), $plugin);
    }
}
