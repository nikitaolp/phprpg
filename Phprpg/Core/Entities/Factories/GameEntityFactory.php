<?php
namespace Phprpg\Core\Entities\Factories;

use Phprpg\Core\Entities\GameEntity;

abstract class GameEntityFactory {
    //put your code here
    protected $blueprints;
    
    
    public function tryToGetRandom():?GameEntity{
        
        $entityWith100percentChance = null;
        
        foreach ($this->blueprints as $k=>$bp){
            
            if ($bp->getChance() == 100){
                $entityWith100percentChance = clone $bp;
            }
            
            $random = round(mt_rand(1, (round((1 / $bp->getChance()) * 1000))));
            //echo $bp->getName()," is name ".$bp->getChance()." is chance, and random thing is $random!<br>";
            if($random == 1 ){
                return clone $bp;
            }
            
        }
        return $entityWith100percentChance;
    }
    
    abstract function fromArray(array $array);
    
}
