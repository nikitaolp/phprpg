<?php
namespace Phprpg\Core\State;
use PDO;
use Phprpg\Core\{Lo,AppStorage};

class Database {
    //put your code here
    
    protected $pdo;
    
    public function __construct(array $config){
        try {
            $this->pdo = new PDO(
                $config['connection'].';dbname='.$config['name'],
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    
    public function install(){
        
    }
    
    public function check(){
        $statement = $this->pdo->prepare("select * from `games`");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_CLASS);
    }
    
    public function getGame($session){
        
        $stmt = $this->pdo->prepare("SELECT * FROM `games` WHERE `session` = :session LIMIT 1");
        $stmt->execute([':session' => $session]);
        return $stmt->fetch();
    }
    
    
    public function getPlayer($session){
        
        $stmt = $this->pdo->prepare("SELECT * FROM `players` WHERE `session` = :session LIMIT 1");
        $stmt->execute([':session' => $session]);
        return $stmt->fetch();
    }
 
//    public function getPlayerAndGame($session){
//        
//        $stmt = $this->pdo->prepare("SELECT * FROM `players` LEFT JOIN `games` ON `players`.`game_id` = `games`.`id` WHERE `players`.`session` = :session LIMIT 1");
//        $stmt->execute([':session' => $session]);
//        return $stmt->fetch();
//    }

    public function getLastPlayer(int $game_id){
        $stmt = $this->pdo->prepare("SELECT * FROM `players` WHERE `game_id` = :game_id ORDER BY `turn_order` DESC LIMIT 1");
        $stmt->execute([':game_id' => $game_id]);
        return $stmt->fetch();
    }
    
    public function getPlayerByTurnOrder(int $game_id, int $turn_order){
        $stmt = $this->pdo->prepare("SELECT * FROM `players` WHERE `game_id` = :game_id AND `turn_order` =:turn_order LIMIT 1");
        $stmt->execute([':game_id' => $game_id,':turn_order' => $turn_order]);
        return $stmt->fetch();
    }
    
    
    //uh why this had player id? we shouldnt get the players last turn, we need to get last turn in general
    public function getLastTurn(int $game_id){
        $stmt = $this->pdo->prepare("SELECT `turns`.`timestamp`,`turns`.`player_id`,`turns`.`game_id`,`players`.`turn_order` FROM `turns` 
            LEFT JOIN `players` ON `players`.`id` = `turns`.`player_id` 
            WHERE `turns`.`game_id` = :game_id   
            ORDER BY `turns`.`timestamp` DESC 
            LIMIT 1");
        $stmt->execute([':game_id' => $game_id]);
        return $stmt->fetch();
    }
    
    public function getPlayersBetweenTurnOrders(int $game_id, int $from, int $to){
        $stmt = $this->pdo->prepare("SELECT * FROM `players` WHERE `game_id` = :game_id AND `turn_order` >= :from AND `turn_order` <= :to ");
        $stmt->execute([':game_id' => $game_id,':from' => $from,':to' => $to]);
        
        
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }
    
    
    public function updateWorld(int $game_id, string $worldstring):void{
        $start = microtime(true);


        $stmt = $this->pdo->prepare("UPDATE `games` SET `world_gz` = :worldstring WHERE `id` = :game_id");
        $stmt->execute([':worldstring'=>$worldstring,':game_id' => $game_id]);
        
        $end = microtime(true);
        Lo::g("World update query took " . ($end - $start) . " seconds.");
    }
    
    
    public function getAllGamePlayers(int $game_id){
        
        $stmt = $this->pdo->prepare("SELECT `turn_order`, `id` FROM `players` WHERE `game_id` = :game_id LIMIT 100");
        $stmt->execute([':game_id' => $game_id]);
        return $stmt->fetchAll(PDO::FETCH_UNIQUE);
    }

    public function insert(string $table,array $parameters){
        $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $table,
                implode(', ',array_keys($parameters)),
                ':'.implode(', :',array_keys($parameters))
                
        );
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($parameters);
            return $this->pdo->lastInsertId();
        } catch(Exception $e){
            die($e->getMessage());
        }
    }
}