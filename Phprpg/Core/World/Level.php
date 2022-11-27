<?php

namespace Phprpg\Core\World;
use Phprpg\Core\VictoryDefeat\VictoryDefeatManager;

/**
 * Description of Level
 *
 * @author Nikita
 */
class Level {
    //put your code here
    
    
    public function __construct(private array $victoryDefeatArray,
                                private array $entityIdArray,
                                private string $name,
                                private int $order,
                                private int $maxMobCount = 50,
                                private int $maxItemCount = 50,
                                private bool $resettable = false){
        
        
    }
    
    public function getEntityIdArray():array{
        return $this->entityIdArray;
    }
    
    public function getOrder():int{
        return $this->order;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function isResettable():bool{
        return $this->resettable;
    }
    
    public function getLevelPassedString(){
        return "<span class=logColor1>{$this->name}</span> <span class=logColor2> - passed!</span>";
    }
    
    public function getVictoryDefeatArray():array{
        return $this->victoryDefeatArray;
    }
    
    public function getMaxMobCount():int{
        return $this->maxMobCount;
    }
    
    public function getMaxItemCount():int{
        return $this->maxItemCount;
    }
}
