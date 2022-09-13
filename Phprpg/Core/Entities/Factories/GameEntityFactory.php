<?php
namespace Phprpg\Core\Entities\Factories;

use Phprpg\Core\Entities\GameEntity;

abstract class GameEntityFactory {
    //put your code here
    protected $blueprints;
    
    
    public function tryToGetRandom():?GameEntity{
        
        $entitiesWith100percentChance = [];
        
        $blue = $this->blueprints;
        
        shuffle($blue);
        
        foreach ($blue as $k=>$bp){
            
            if ($bp->getChance() == 100){
                $entitiesWith100percentChance[] = clone $bp;
            }
            
            $random = round(mt_rand(1, (round((1 / $bp->getChance()) * 1000))));

            if($random == 1 ){
                return clone $bp;
            }
            
        }
        
        shuffle($entitiesWith100percentChance);
        
        if (!empty($entitiesWith100percentChance[0])){
            return $entitiesWith100percentChance[0];
        }
        
        return null;
    }
    
    abstract function fromArray(array $array):void;
    

    public function getByEntityId(int $entity_id):?GameEntity{
        
        if (!empty($this->blueprints[$entity_id])){
            return clone $this->blueprints[$entity_id];
        }
        
        return null;
    }
    
}
