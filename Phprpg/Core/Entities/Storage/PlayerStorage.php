<?php

namespace Phprpg\Core\Entities\Storage;

use Phprpg\Core\Turns\Coordinates;
use Phprpg\Core\{Lo};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};
/**
 * Description of PlayerStorage
 *
 * @author Nikita
 */
class PlayerStorage extends MobStorage{
    
    private array $deadPlayers = [];
    
    
    public function getDeadPlayers():array{
        return $this->deadPlayers;
    }
    
    public function isPlayerDead(int $id):bool{

        return in_array($id,$this->deadPlayers);
       
    }
    
    public function addDeadPlayer(int $id):void{
        
        $this->deadPlayers[] = $id;

    }
    
    
    
    
    public function unsetEntity(Coordinates $coord){
        
        $x = $coord->getX();
        $y = $coord->getY();
        
        if (!empty($this->storage[$y][$x])){
            $this->addDeadPlayer($this->storage[$y][$x]->getId());
        }
            
        
        
        unset($this->storage[$y][$x]);
    }
   
}
