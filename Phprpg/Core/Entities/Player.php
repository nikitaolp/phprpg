<?php
namespace Phprpg\Core\Entities;

class Player extends Mob {
    
    private int $player_id = 0;
    
    public function setId(int $id){
        $this->player_id = $id;
    }
    
    public function getId(){
        return $this->player_id;
    }
}
