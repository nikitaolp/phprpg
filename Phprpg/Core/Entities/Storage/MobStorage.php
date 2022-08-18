<?php
namespace Phprpg\Core\Entities\Storage;

use Phprpg\Core\Turns\Coordinates;
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};

class MobStorage extends GameEntityStorage{
    //put your code here
    
    public function checkForAttackableMob(Mob $mob, Coordinates $coord):?Mob{
        if ($defender = $this->getEntity($coord)){
            if ($mob->getTeam() != $defender->getTeam()){
                return $defender;
            }
        }
        return null;
    }
}
