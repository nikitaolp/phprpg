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
            Lo::gG("<span class='logColor1'>{$this->attacker->getNickname()}</span> killed <span class='logColor2'>{$this->defender->getNickname()}</span>");
            $this->attacker->affectXp($this->defender->getXpValue());
            $this->attacker->receiveInventory($this->defender->getInventory());
        }
    }
   
    
}
