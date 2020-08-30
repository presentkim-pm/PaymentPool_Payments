<?php

/**
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
 * @author  Blugin-team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 * @name PaymentPointAPIProvider
 * @api 4.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\PaymentPointAPIProvider
 */

namespace blugin\api\paymentpool;

use Leader\PointAPI;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class PaymentPointAPIProvider extends PluginBase{
    public function onLoad(){
        if(!class_exists(PaymentPool::class))
            throw new \RuntimeException("Could not load provider of 'PointAPI': PaymentPool missing");
        if(!class_exists(PointAPI::class))
            throw new \RuntimeException("Could not load provider of 'PointAPI': Target payment class missing");

        PaymentPool::getInstance()->registerProvider(new class() implements IPaymentProvider{
            public function getName() : string{
                return "pointapi";
            }

            /** @return float[] player name => money */
            public function getAll() : array{
                return array_map(function(array $data) : float{
                    return (float) $data["Point"];
                }, PointAPI::getInstance()->Pt);
            }

            public function exists($player) : bool{
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                return isset(PointAPI::getInstance()->Pt[$player]);
            }

            public function create($player, float $value) : bool{
                if($this->exists($player))
                    return false;

                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                PointAPI::getInstance()->Pt[$player]["Point"] = (int) $value;
                return true;
            }

            public function get($player) : ?float{
                if(!$this->exists($player))
                    return null;
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                return (float) PointAPI::getInstance()->Pt[$player]["Point"];
            }

            public function set($player, float $value) : void{
                if(!$this->exists($player))
                    return;
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                PointAPI::getInstance()->Pt[$player]["Point"] = (int) $value;
            }

            public function increase($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                PointAPI::getInstance()->Pt[$player]["Point"] += (int) $value;
                return $this->get($player);
            }

            public function decrease($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                PointAPI::getInstance()->Pt[$player]["Point"] -= (int) $value;
                return $this->get($player);
            }
        }, ["leader:pointapi", "point"]);
    }
}