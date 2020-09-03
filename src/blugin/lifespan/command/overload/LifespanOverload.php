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

use blugin\lifespan\lib\command\BaseCommand;
use blugin\lifespan\lib\command\handler\ICommandHandler;
use blugin\lifespan\lib\command\overload\NamedOverload;
use blugin\lifespan\lib\command\parameter\defaults\FloatParameter;

abstract class LifespanOverload extends NamedOverload implements ICommandHandler{
    public function __construct(BaseCommand $baseCommand, string $name){
        parent::__construct($baseCommand, $name);
        $this->addParamater((new FloatParameter("seconds"))->setMin(0)->setMax(0x7fff));
        $this->setHandler($this);
    }
}