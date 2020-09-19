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

namespace blugin\lifespan\command\overload;

use blugin\lib\command\BaseCommand;
use blugin\lib\command\handler\ICommandHandler;
use blugin\lib\command\overload\NamedOverload;
use blugin\lib\command\overload\Overload;
use blugin\lib\command\parameter\defaults\FloatParameter;
use blugin\lifespan\Lifespan;
use pocketmine\command\CommandSender;

class LifespanOverload extends NamedOverload implements ICommandHandler{
    public function __construct(BaseCommand $baseCommand, string $mode){
        if(($default = Lifespan::DEFAULTS[$mode] ?? null) === null)
            throw new \InvalidArgumentException("Mode '$mode' is invalid");

        parent::__construct($baseCommand, $mode);
        $this->addParamater((new FloatParameter("seconds"))->setMin(0)->setMax((0x8000 + $default) / 20));
        $this->setHandler($this);
    }

    /** @param mixed[] $args name => value */
    public function handle(CommandSender $sender, array $args, Overload $overload) : bool{
        Lifespan::getInstance()->setLifespan($this->getLabel(), (int) (($args["seconds"]) * 20));
        $this->sendMessage($sender, "success", [(string) $args["seconds"]]);
        return true;
    }
}