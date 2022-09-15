<?php

namespace Phprpg\Core\World;
/**
* WorldBuilder class
* 
* I don't like this class.
* It shouldn't be called WorldBuilder, it doesn't build worlds, it builds itself
* Since this object is pretty much the only thing stored in DB, it should be more like WorldState, with all the building functions moved to some LevelBuilder class
* but I'm keeping it like this for now
* 
* @package    phprpg
* @author     nikitaolp
*/
use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Entities\Factories\{TileFactory,MobFactory,PlayerFactory,ItemFactory,GameEntityFactory};
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,GameEntityStorage};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};
use Phprpg\Core\Turns\Coordinates;
use Phprpg\Core\VictoryDefeat\{VictoryDefeat};

class WorldBuilder {

    private int $maxMobCount;
    private int $maxItemCount;
    
    private int $height;
    private int $width;
    private ?VictoryDefeat $victoryDefeat = null;
    private ?Level $level = null;
    private array $playerSpawnCoordinates = [];
    
    private array $deadPlayers = [];
    
    public function __construct(
            private TileFactory $tileAssembler, 
            private MobFactory $mobAssembler,
            private PlayerFactory $playerAssembler,
            private ItemFactory $itemAssembler,
            private TileStorage $tileStorage,
            private MobStorage $mobStorage,
            private GameEntityStorage $itemStorage
            ){
        
            $this->height = AppStorage::get('cfg','height');
            $this->width = AppStorage::get('cfg','width');
        
            $this->tileAssembler->fromArray(AppStorage::get('tiles'));
            $this->mobAssembler->fromArray(AppStorage::get('mobs'));
            $this->itemAssembler->fromArray(AppStorage::get('items'));
            $this->playerAssembler->fromArray(AppStorage::get('players'));
            
    }
    
    public function setVictoryDefeat(VictoryDefeat $vd):void{
        $this->victoryDefeat = $vd;
    }
    
    public function getVictoryDefeat(): ?VictoryDefeat{
        return $this->victoryDefeat;
    }
    
    public function getDeadPlayers():array{
        return $this->deadPlayers;
    }
    
    public function isPlayerDead(int $id):bool{

        return in_array($id,$this->deadPlayers);
       
    }
    
    public function addDeadPlayer(int $id):void{
        
        $this->deadPlayers[] = $id;

    }
    
    public function getWorldTiles():array{
        return $this->tileStorage->getEntities();
    }
    
    public function getMobs():array{
        return $this->mobStorage->getEntities();
    }
    
    public function getMobStorage():GameEntityStorage{
        return $this->mobStorage;
    }
    
    public function getItemStorage():GameEntityStorage{
        return $this->itemStorage;
    }
    
    
    public function getTileStorage():TileStorage{
        return $this->tileStorage;
    }
    
    
    public function setLevel(Level $level):void{
        $this->level = $level;
    }
    
    public function getLevel():?Level{
        return $this->level;
    }
    
    public function addPlayer(int $player_id):void{
        
        
        
        $random_location = $this->tileStorage->getRandomTileCoordinates();
        $surroundingTiles = $this->tileStorage->getSurroundingEntities($random_location,5);

        foreach($surroundingTiles as $y => $line){
            

            foreach ($line as $x=>$tile){
                if ($player = $this->tryToFillTile($tile,$this->mobStorage,$this->playerAssembler, $x, $y)){

                    $player->setId($player_id);
                    
                    break;
                }
            }
            if (!empty($player)){
                break;
            }

        }  
    }
    
    //world array should be GameEntityStorage type object, not just array. But how to "build" then? Maybe just build here and then set the entire array in object
    //ok world array is gone, long live tile storage
    public function buildRandom():void{
        
            for ($h = 0; $h<= $this->height-1; $h++){
            
                //$this->worldArray[$h] = [];
                    
                for ($w = 0; $w<= $this->width-1; $w++){
                       $randomTile = $this->tileAssembler->tryToGetRandom();
                       if (!$randomTile) {
                           throw new Exception("Random tile is null, default tile with 100 chance likely not set in cfg");
                       } else {
                           
                           $this->tileStorage->storeAtXY($randomTile, $w, $h);
                           
                           //$this->tileStorage->getEntities();
                           //$this->tileStorage->getEntityByXY()
                           
                           //$this->worldArray[$h][$w] = $randomTile;
                           
                           if (!$this->tryToFillTile($this->tileStorage->getEntityByXY($w,$h),$this->mobStorage,$this->mobAssembler, $w, $h)){
                               $this->tryToFillTile($this->tileStorage->getEntityByXY($w,$h),$this->itemStorage,$this->itemAssembler, $w, $h);
                           }
                           

                       }
                       
                }
            
        }
        
     $this->maxMobCount = $this->mobStorage->getAllEntityCount();   
     $this->maxItemCount = $this->itemStorage->getAllEntityCount();  

    }
    
    public function tryToFillTile(Tile $tile, GameEntityStorage $storage, GameEntityFactory $factory, $x, $y):?GameEntity{
        
        
        if ($tile->isWalkable() && !($this->mobStorage->getEntityByXY($x,$y)) && !($this->itemStorage->getEntityByXY($x,$y)) ){
            $mob = $factory->tryToGetRandom();
            if ($mob){
                $storage->storeAtXY($mob,$x,$y);
                return $mob;
                //Lo::g($mob->getNickname()." is at $x $y , i guess ".$tile->getName().' is walkable...');
            }
        }
        return null;
    }
    
    
    public function topUpEntities():void{
        $this->tryToSpawnEntities($this->itemStorage,$this->itemAssembler,$this->maxItemCount);
        $this->tryToSpawnEntities($this->mobStorage,$this->mobAssembler,$this->maxMobCount);
    }
    
    
    public function tryToSpawnEntities(GameEntityStorage $storage, GameEntityFactory $factory,int $max_count):void{
        
        $remainingItems = $storage->getAllEntityCount();
        
        if ($remainingItems < round(($max_count/100)*50)){
            
            $random_location = $this->tileStorage->getRandomTileCoordinates();
            
            $surroundingTiles = $this->tileStorage->getSurroundingEntities($random_location,5);

            foreach($surroundingTiles as $y => $line){

                foreach ($line as $x=>$tile){
                    $this->tryToFillTile($tile,$storage,$factory, $x, $y);
                }

            }  
            
        }
        

    }
    
    
    
    //old repopulation method that used map edges, more realistic, but impractical on larger maps as all the action happened on the edges and the center was empty
    public function tryToRepopulate():void{
        
        $remainingMobs = $this->mobStorage->getAllEntityCount();
        
        

        if ($remainingMobs < round(($this->maxMobCount/100)*50)){
            
           
            
            foreach($this->tileStorage->getEntities() as $y => $line){

                    foreach ($line as $x=>$tile){
                        if ($y == 0 || $y == array_key_last($this->tileStorage->getEntities()) || $x == 0 || $x == array_key_last($line)){
                            $this->tryToFillTile($tile,$this->mobStorage,$this->mobAssembler, $x, $y);
                        }
                        
                    }
                
            }         
        }
        

    }
    
    private function getPlayers():array{

        $mobs = $this->getMobs();
        
        $players = [];
        
        foreach ($mobs as $y => $line){

            foreach ($line as $x => $mob){
                //these class checks are bad
                if ('Phprpg\Core\Entities\Player' != get_class($mob) || $mob->isExpired()){
                    continue;
                }
                
                $players[] = $mob;
                
            }

        }

        return $players;

    }
    
    public function build():void{
        
        if (!(empty($this->level->getEntityIdArray()))){
            $this->buildFromLevelArray($this->level->getEntityIdArray());
        } else {
            $this->buildRandom();
        }
        
    }
     
    
    private function buildFromLevelArray(array $level_array):void{
        
        $tiles = clone $this->tileStorage;
        $tiles->clearStorage();
        
        $mobs = clone $this->mobStorage;
        $mobs->clearStorage();
        
        $items = clone $this->itemStorage;
        $items->clearStorage();
        
        $this->playerSpawnCoordinates = [];
        
        
        foreach ($level_array as $y => $x_array){
            
            foreach ($x_array as $x => $tile_placeholder){
            
                if (!is_array($tile_placeholder)){
                    
                    $tiles->storeAtXY($this->tileAssembler->getByEntityId($tile_placeholder),$x,$y);
                    
                } else {
                    
                    if (!empty($tile_placeholder[0])){
                        
                        $tiles->storeAtXY($this->tileAssembler->getByEntityId($tile_placeholder[0]),$x,$y);
                        
                        if (!empty($tile_placeholder[1])){
                            
                            $entity_id_type = (string)$tile_placeholder[1];
                            $entity_id_type = $entity_id_type[0];
                            
                            switch ($entity_id_type) {
                                case '2':
                                    $mobs->storeAtXY($this->mobAssembler->getByEntityId($tile_placeholder[1]),$x,$y);
                                break;

                                case '3':
                                    $items->storeAtXY($this->itemAssembler->getByEntityId($tile_placeholder[1]),$x,$y);
                                break;

                                case '4':
                                    $mobs->storeAtXY($this->mobAssembler->getByEntityId($tile_placeholder[1]),$x,$y);
                                break;
                                
                                case '9':
                                    if (999 == $tile_placeholder[1]){
                                        $this->playerSpawnCoordinates[] = new Coordinates($x,$y);
                                    }
                                break;
                            }
                            
                        }
                        
                    } else {
                        throw new Exception("Something weird with level config");
                    }
                    
                }

            }
            
        }
        
        
        if ($players = $this->getPlayers()){
            
            foreach ($players as $k => $pl){
                
                if (!empty($this->playerSpawnCoordinates[$k])){
                    $mobs->storeAtXY($pl,$this->playerSpawnCoordinates[$k]->getX(),$this->playerSpawnCoordinates[$k]->getY());
                } else {
                    throw new Exception("there are fewer player spawn points than there are players");
                }
                
                
                
            }
            
        }
        
        $this->tileStorage = $tiles;

        
        $this->mobStorage = $mobs;

        
        $this->itemStorage = $items;
        $this->victoryDefeat = null;
        
        $this->maxMobCount = $this->mobStorage->getAllEntityCount();   
        $this->maxItemCount = $this->itemStorage->getAllEntityCount(); 
        
    }

}
