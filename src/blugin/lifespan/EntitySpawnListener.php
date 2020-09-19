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
    private function __construct(){ }

    /** @priority MONITOR */
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
            $itemLifeProperty->setValue($entity, min(0x7fff, max(0, $before + 6000 - Lifespan::getInstance()->getLifespan(Lifespan::ITEM))));
        }elseif($entity instanceof Arrow){
            static $arrowLifeProperty = null;
            if($arrowLifeProperty === null){
                $arrowReflection = new \ReflectionClass(Arrow::class);
                $arrowLifeProperty = $arrowReflection->getProperty("collideTicks");
                $arrowLifeProperty->setAccessible(true);
            }

            $before = $arrowLifeProperty->getValue($entity);
            $arrowLifeProperty->setValue($entity, min(0x7fff, max(0, $before + 1200 - Lifespan::getInstance()->getLifespan(Lifespan::ARROW))));
        }
    }

    public static function register(Plugin $plugin) : void{
        $plugin->getServer()->getPluginManager()->registerEvents(new self(), $plugin);
    }
}
