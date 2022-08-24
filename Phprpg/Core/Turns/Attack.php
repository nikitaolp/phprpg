<?php
namespace Phprpg\Core\Turns;

use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Entities\{GameEntity,Mob,Player};


class Attack {
    //put your code here
    
    public function __construct(protected Mob $attacker, protected Mob $defender){
        $this->fight();
    }
    
    private function fight():void{
        $this->defender->affectHp(-1*$this->attacker->getDmg());
        $this->attacker->addStatus('attack');
        if ($this->defender->isExpired()){
            Lo::g("{$this->attacker->getNickname()} killed {$this->defender->getNickname()}");
            $this->attacker->affectXp($this->defender->getXpValue());
            $this->attacker->receiveInventory($this->defender->getInventory());
        }
    }
   
    
}
