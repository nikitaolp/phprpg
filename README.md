# phprpg
Turn based game engine prototype. Now includes a PHP Sokoban implementation!

This is a study project, i don't recommend installing this on a production server

Demo: https://phprpg.cf/

Database config in /Phprpg/cred.php

Tables are created by running /Phprpg/install.php

Randomized world is based on tile config (/Phprpg/Config/tiles.php)

Mobs are based on mob config (/Phprpg/Config/mobs.php)

Mobs can: attack the player and each other (according to "team" config parameter), pick up items

Mobs have basic AI: they walk around in random directions, then try to approach objects of interest (players, items)
and act according to /Phprpg/Config/cfg.php mob_priorities parameter

'escape' - run away on low health. Annoying, to be honest

'pickup_desireable' - pick up items marked as desireable

'attack' - attack player or mobs from other teams

'avoid_undesireable' - try to not step on "undesireable" items, like harmful Spikes. At this point mobs only decide to step on spikes while running away from danger

Items are based on item config (/Phprpg/Config/items.php)

Can be desireable, undesireable or neutral ('desireable'=>null) for mobs. Mobs will interact with neutral items, but only if they stepped on them by accident, neutral items don't affect pathfinding

Inventory is barely functioning at this point, player and mobs can pick up gold, but it doesn't do anything, except if used in victory conditions

Victory conditions (/Phprpg/Config/victory.php)

Right now, victory/defeat only show a message, and that's it

'players' => ['all_dead'=>true]

condition is true when all the "player slots" are taken and players are dead

Player slots are set in /Phprpg/Config/cfg.php player_limit parameter. Basically, this is a limit of players that can join the game.

Since player can rejoin the game upon death, this parameter is basically "number of lives" too

Pretty much the entirety of game data is stored in games/world_gz database column. 

This is a gzipped stringified PHP object, which is a HUGE MESS, but it's "good enough" for now, considering the scope of the project and brevity of our lives
