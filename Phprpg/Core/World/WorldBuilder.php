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
 * 
 * private TileStorage $tileStorage,
            private MobStorage $mobStorage,
            private GameEntityStorage $itemStorage,
            private GameEntityStorage $pushableBlockStorage
* @package    phprpg
* @author     nikitaolp
*/
use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Entities\Factories\{TileFactory,MobFactory,PlayerFactory,ItemFactory,GameEntityFactory,PushableBlockFactory};
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,PlayerStorage,GameEntityStorage,StorageBundle};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player,PushableBlock};
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
            private PushableBlockFactory $pushableBlockAssembler,
            private StorageBundle $storage
            ){
        
            $this->height = AppStorage::get('cfg','height');
            $this->width = AppStorage::get('cfg','width');
        
            $this->tileAssembler->fromArray(AppStorage::get('tiles'));
            $this->mobAssembler->fromArray(AppStorage::get('mobs'));
            $this->itemAssembler->fromArray(AppStorage::get('items'));
            $this->playerAssembler->fromArray(AppStorage::get('players'));
            $this->pushableBlockAssembler->fromArray(AppStorage::get('pushables'));
            
    }
    
    public function setVictoryDefeat(VictoryDefeat $vd):void{
        $this->victoryDefeat = $vd;
    }
    
    public function getVictoryDefeat(): ?VictoryDefeat{
        return $this->victoryDefeat;
    }
    
    
    public function getStorageBundle(){
        return $this->storage;
    }
    
    
    public function setLevel(Level $level):void{
        $this->level = $level;
    }
    
    public function getLevel():?Level{
        return $this->level;
    }
    
    public function addPlayer(int $player_id):void{
        
        
        if (!empty($this->playerSpawnCoordinates)){
            if (!($player = $this->addPlayerAtSpawnCoordinates())){
                throw new Exception("Unable to add player to predefined player coordinates");
            }
            
        } else {
            $player = $this->addPlayerRandom();
        }
        
        $player->setId($player_id);
        

    }
    
    private function addPlayerAtSpawnCoordinates():?Player{
        
        $new_player = $this->playerAssembler->tryToGetRandom();
        
        foreach ($this->playerSpawnCoordinates as $coord){
            
            if ($this->storage->getTileStorage()->checkTileWalkability($coord) && !$this->storage->getMobStorage()->getEntity($coord)){
                
                $this->storage->getStorage('Player')->storeAtXY($new_player,$coord->getX(),$coord->getY());
                
                return $new_player;
            }
            
        }
        
        return null;
    }
    
    private function  addPlayerRandom():?Player{
        
        $random_location = $this->storage->getStorage('Tile')->getRandomTileCoordinates();
        $surroundingTiles = $this->storage->getStorage('Tile')->getSurroundingEntities($random_location,5);

        foreach($surroundingTiles as $y => $line){
            

            foreach ($line as $x=>$tile){
                if ($player = $this->tryToFillTile($tile,$this->storage->getStorage('Player'),$this->playerAssembler, $x, $y)){
                    
                    return $player;   
                }
            }

        } 
        
        return null;
        
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
                           
                           $this->storage->getStorage('Tile')->storeAtXY($randomTile, $w, $h);
                           
                           //$this->storage->getStorage('Tile')->getEntities();
                           //$this->storage->getStorage('Tile')->getEntityByXY()
                           
                           //$this->worldArray[$h][$w] = $randomTile;
                           
                           if (!$this->tryToFillTile($this->storage->getStorage('Tile')->getEntityByXY($w,$h),$this->storage->getStorage('Mob'),$this->mobAssembler, $w, $h)){
                               $this->tryToFillTile($this->storage->getStorage('Tile')->getEntityByXY($w,$h),$this->storage->getStorage('Item'),$this->itemAssembler, $w, $h);
                           }
                           

                       }
                       
                }
            
        }
        
     $this->maxMobCount = $this->storage->getStorage('Mob')->getAllEntityCount();   
     $this->maxItemCount = $this->storage->getStorage('Item')->getAllEntityCount();  

    }
    
    public function tryToFillTile(Tile $tile, GameEntityStorage $storage, GameEntityFactory $factory, $x, $y):?GameEntity{
        
        
        if ($tile->isWalkable() && !($this->storage->getStorage('Mob')->getEntityByXY($x,$y)) && !($this->storage->getStorage('Item')->getEntityByXY($x,$y)) ){
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
        $this->tryToSpawnEntities($this->storage->getStorage('Item'),$this->itemAssembler,$this->maxItemCount);
        $this->tryToSpawnEntities($this->storage->getStorage('Mob'),$this->mobAssembler,$this->maxMobCount);
    }
    
    
    public function tryToSpawnEntities(GameEntityStorage $storage, GameEntityFactory $factory,int $max_count):void{
        
        $remainingItems = $storage->getAllEntityCount();
        
        if ($remainingItems < round(($max_count/100)*50)){
            
            $random_location = $this->storage->getStorage('Tile')->getRandomTileCoordinates();
            
            $surroundingTiles = $this->storage->getStorage('Tile')->getSurroundingEntities($random_location,5);

            foreach($surroundingTiles as $y => $line){

                foreach ($line as $x=>$tile){
                    $this->tryToFillTile($tile,$storage,$factory, $x, $y);
                }

            }  
            
        }
        

    }
    
    
    
    //old repopulation method that used map edges, more realistic, but impractical on larger maps as all the action happened on the edges and the center was empty
    public function tryToRepopulate():void{
        
        $remainingMobs = $this->storage->getStorage('Mob')->getAllEntityCount();
        
        

        if ($remainingMobs < round(($this->maxMobCount/100)*50)){
            
           
            
            foreach($this->storage->getStorage('Tile')->getEntities() as $y => $line){

                    foreach ($line as $x=>$tile){
                        if ($y == 0 || $y == array_key_last($this->storage->getStorage('Tile')->getEntities()) || $x == 0 || $x == array_key_last($line)){
                            $this->tryToFillTile($tile,$this->storage->getStorage('Mob'),$this->mobAssembler, $x, $y);
                        }
                        
                    }
                
            }         
        }
        

    }
    
    
    public function build():void{
        
        $this->victoryDefeat = null;
        
        if (!(empty($this->level->getEntityIdArray()))){
            
            $this->maxMobCount = $this->level->getMaxMobCount();
            $this->maxItemCount = $this->level->getMaxItemCount();
            
            $this->buildFromLevelArray($this->level->getEntityIdArray());
        } else {
            
            $this->maxMobCount = $this->storage->getStorage('Mob')->getAllEntityCount();   
            $this->maxItemCount = $this->storage->getStorage('Item')->getAllEntityCount(); 
            
            $this->buildRandom();
        }
        
    }
     
    
    private function buildFromLevelArray(array $level_array):void{
        
        $tiles = clone $this->storage->getStorage('Tile');
        $tiles->clearStorage();
        
        $mobs = clone $this->storage->getStorage('Mob');
        $mobs->clearStorage();
        
        $playerStorage = clone $this->storage->getStorage('Player');
        $playerStorage->clearStorage();
        
        $items = clone $this->storage->getStorage('Item');
        $items->clearStorage();
        
        $pushables = clone $this->storage->getStorage('PushableBlock');
        $pushables->clearStorage();
        
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
                            
                                case '5':
                                    $pushables->storeAtXY($this->pushableBlockAssembler->getByEntityId($tile_placeholder[1]),$x,$y);
                                    Lo::g($this->storage->getPushableBlockStorage()->getEntityByXY($x,$y));
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
        
        
        if ($players = $this->getStorageBundle()->getPlayerStorage()->getEntities()){
            
            $k = 0;
            
            foreach ($players as $y => $xarr){
                
                foreach ($xarr as $x => $pl){
                    

                    if (!empty($this->playerSpawnCoordinates[$k])){
                        $playerStorage->storeAtXY($pl,$this->playerSpawnCoordinates[$k]->getX(),$this->playerSpawnCoordinates[$k]->getY());
                    } else {
                        throw new \Exception("there are fewer player spawn points than there are players");
                    }
                
                    $k++;
                }
                
            }
            
        }
        
        $this->storage->setStorage('Tile',$tiles);

        
        $this->storage->setStorage('Mob',$mobs);

        $this->storage->setStorage('Player',$playerStorage);
        
        $this->storage->setStorage('Item',$items);
        
        $this->storage->setStorage('PushableBlock',$pushables);
        
        $this->victoryDefeat = null;
        
        
        
    }

}
