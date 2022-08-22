<?php
namespace Phprpg\Core\State;

use Phprpg\Core\AppStorage;
use Phprpg\Core\Lo;
use Phprpg\Core\World\WorldBuilder;

class GameState {
    //put your code here
    
    private string $game_session_name;
    private ?WorldBuilder $world = null;
    private ?int $game_id = null;
    private ?int $player_id;
    private bool $new_player = false;
    private ?int $turn_order;
    private ?int $max_turn_order = null;
    private string $message = '';
    
    
    public function __construct(array $post){
        
        $this->game_session_name = 'game_'.AppStorage::get('cfg','type');
        $this->player_session_name = 'player_'.AppStorage::get('cfg','type');
        
        
        if (!empty($post['code'])){
            
            $this->joinGame(trim($post['code']));
        }
        
        if (!empty($post['newgame'])){
            $this->newGame();
        }
        
        
        if ($this->initGame()){
            if ($this->game_id){
                if (!$this->initPlayer($this->game_id)){

                    $this->createPlayer($this->game_id);
                }
            }

            Lo::g("Game ID {$this->game_id}");
            Lo::g("Player ID {$this->player_id}");
            Lo::g("Turn order {$this->turn_order}");

            if (!$this->max_turn_order){
                $order = $this->findMaxTurnOrder();
                if (!$order){
                    $this->max_turn_order = 1;
                }
            }
        }
        
        
        

        
    }
    
    public function getPlayerSlots():string{
        return $this->max_turn_order.'/'.AppStorage::get('cfg','player_limit');
    }
    
    public function getJoinCode():?string{
        if (!empty($_SESSION[$this->game_session_name]) && $this->max_turn_order < AppStorage::get('cfg','player_limit')){
            return $_SESSION[$this->game_session_name];
        }
        return null;
    }
    
    public function isGameStarted():bool{
        if ($this->game_id){
            return true;
        }
        return false;
    }
    
    public function isPlayerNew():bool{
        return $this->new_player;
    }
    
    public function getMessage():?string{
        return $this->message;
    }
    
    private function newGame(){
        $this->quitGame();
        $this->createGame();
        header("Location: /");
        die();
    }
    
    
    private function joinGame(string $session){
        if (AppStorage::get('db')->getGame($session)){
            $_SESSION[$this->game_session_name] = $session; 
            unset($_SESSION[$this->player_session_name]);
        }
        header("Location: /");
        die();
    }
    
    
    public function checkIfLastTurn(){
        
        $max = $this->max_turn_order;
        

        $all_players = AppStorage::get('db')->getAllGamePlayers($this->game_id);
        krsort($all_players);
        
        $alive_player_found = false;
        
        if ($all_players){
            foreach ($all_players as $turn_order=>$pl){
                if (!$this->world->isPlayerDead($pl['id'])){
                    $max = $turn_order;
                    $alive_player_found = true;
                    break;
                }
            }            
        }
        
        
        if ($this->turn_order == $max || !$alive_player_found){
            return true;
        }
        return false;
    }
    
    public function getWorld():?WorldBuilder{
        return $this->world;
    }
    
    public function setWorld(WorldBuilder $world):void{
         $this->world = $world;
    }
    
    public function getPlayerId(){
        return $this->player_id;
    }
    
    public function saveWorld(WorldBuilder $world):void{
        $worldstring = gzcompress(serialize($world));
        
        AppStorage::get('db')->updateWorld($this->game_id,$worldstring);
    }
    
    private function initGame():bool{
        
        if(!empty($_SESSION[$this->game_session_name])){
            
            $db_game = AppStorage::get('db')->getGame($_SESSION[$this->game_session_name]);
            
            if ($db_game){
                $this->game_id = $db_game['id'];
                
                if (!empty($db_game['world_gz'])){

                    $world = unserialize(gzuncompress($db_game['world_gz']));
                    $world->getVictoryDefeat();
                    if ($world){
                        $this->world = $world;
                    }
                }
                
                
                return true;
            } else {
                unset($_SESSION[$this->game_session_name]);
            }
        }
        
        return false;
    }
    
    public function quitGame():void{
        unset($_SESSION[$this->player_session_name]);
        unset($_SESSION[$this->game_session_name]);
    }
    
    private function createGame():bool{
        
        $game_session_value = hash('sha256',time().random_bytes(10));
        $_SESSION[$this->game_session_name] = $game_session_value;
        
        $this->game_id = AppStorage::get('db')->insert('games',['session'=>$game_session_value,'world_gz'=>'']);
        
        if ($this->game_id){
            return true;
        }
        
        return false;
        //insert game into database, set ID, 
    }
    
    private function findMaxTurnOrder(){
        $last_player = AppStorage::get('db')->getLastPlayer($this->game_id);
        if ($last_player){
            $this->max_turn_order = $last_player['turn_order'];
        } 
        return $this->max_turn_order;
    }
    
    private function initPlayer(int $game_id){
        
        
        if(!empty($_SESSION[$this->player_session_name])){
            $db_player_game = AppStorage::get('db')->getPlayer($_SESSION[$this->player_session_name]);
            if ($db_player_game){
                $this->player_id = $db_player_game['id'];
                $this->turn_order = $db_player_game['turn_order'];
                
                
                
                return true;
            } else {
                unset($_SESSION[$this->player_session_name]);
            }
            
        }
        
        return false;
    }
    
    public function createPlayer(int $game_id):bool{
        
        $last_player = $this->findMaxTurnOrder();

        if ($last_player && $last_player<=AppStorage::get('cfg','player_limit')-1){
            $player_turn_order = $last_player + 1;
        } else if ($last_player && $last_player>=AppStorage::get('cfg','player_limit')){
            $player_turn_order = false;
            Lo::g("Number of players exceeded");
        } else if (!$last_player){
            $player_turn_order = 1;
        }
        
        if ($player_turn_order){
            $personal_session = hash('sha256',time().random_bytes(10));
            
            $_SESSION[$this->player_session_name] = $personal_session;
            
            $this->player_id = AppStorage::get('db')->insert('players',['game_id'=>$game_id,'session'=>$personal_session,'turn_order'=>$player_turn_order]);
            $this->turn_order = $player_turn_order;
            $this->new_player = true;
            return true;
        }
        return false;
    }
    

    
    public function completeTurn():void{
        AppStorage::get('db')->insert('turns',['game_id'=>$this->game_id,'player_id'=>$this->player_id]);
        
    }
    
    


    public function checkIfYourTurnV3(){
        //the problem with this method is that it returns false if player was killed by mobs during his turn while there are more than 1 players active... or something
        $last_turn_info = AppStorage::get('db')->getLastTurn($this->game_id);
        

        $current_turn_order = 1;

        if ($last_turn_info){
            if ($last_turn_info['turn_order'] == $this->max_turn_order){
                $current_turn_order = 1;
            } else {
                $current_turn_order = $last_turn_info['turn_order'] + 1;
            }
            
            
            
            $timeout = AppStorage::get('cfg','turn_timeout');

            $last_turn_time = strtotime($last_turn_info['timestamp']);
            
            $current_time = time();
            
            if ($last_turn_time + $timeout <= $current_time){
                
                
                //this means the player is late
                //need to find out how much time exactly passed between last turn and current time 
                //and divide it by timeout, to get how many turns were skipped
                
                $skipped_time = $current_time - $last_turn_time;
                
                
                
                $number_of_turns_skipped = intdiv($skipped_time, $timeout);
               
                $number_of_players = $this->max_turn_order;
                
                Lo::g("since last turn:$skipped_time turns skipped: $number_of_turns_skipped players: $number_of_players");
                
                //when was the last time i have unironically used while loop? isn't it made for such cases? whatever.
                for ($i = $current_turn_order;$i<=$number_of_turns_skipped;$i++){
                   
                    $current_turn_order++;
                    
                    if ($current_turn_order > $number_of_players){
                        $current_turn_order = 1;
                    }
                    
                }
            }
            
        }
        
        //now i need another loop, through all the existing players, to check if current player is dead, check next one, check if dead...
        
        //Lo::g("current turn order: before death check $current_turn_order");
        
 

        $all_players = AppStorage::get('db')->getAllGamePlayers($this->game_id);



        if ($all_players){

            if ($this->world->isPlayerDead($all_players[$current_turn_order]['id'])){
                $all_players_sorted = $all_players;


                foreach ($all_players as $turn_order => $player){

                    if ($turn_order <= $current_turn_order){

                        //moving "past" turn order players to the end of array, at least that's how i hope it to turn out
                        unset($all_players_sorted[$turn_order]);
                        $all_players_sorted[$turn_order] = $player;

                    } else {
                        break;
                    }

                }
                
                unset($all_players_sorted[$current_turn_order]);
                
                foreach ($all_players_sorted as $turn_order => $cur_pl){
                    if (!$this->world->isPlayerDead($cur_pl['id'])){
                        $current_turn_order = $turn_order;
                        break;
                    }
                }

            }





        }
            
            
        //Lo::g("current turn order: after death check $current_turn_order");
        
        if ($current_turn_order == $this->turn_order){
            $this->message = "it's your turn";
            return true;
        } else {
            $this->message = "it's NOT your turn";
        }
        
        return false;
        
        
        
    }

}
/*

 *  * ok so... for the current turn check 
 * 
 * 
 * 
 * $number_of_players = 3;
 * 
 * $turns_skipped = 5;
 * 
 * $current_turn = 1;
 * 
 * for ($i = 1;$i>=$turns_skipped;$i++){
 * 
 *  if ($i>=$number_of_players) {
 *  $current_turn = 1;
 * }
 * 
 * }
 * 
 */