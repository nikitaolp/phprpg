<?php

return [
    'health'=>[
        'gfx'=>'heart.png',
        'entity_id'=>'301',
        'desc'=>'Heal up!',
        'chance'=>5,
        'desireable'=>true,
        'action'=>['maxhp'=>50,'hp'=>300]
    ],
    'spikes'=>[
        'gfx'=>'spikes.png',
        'entity_id'=>'302',
        'desc'=>'Spike trap. Bad to step on.',
        'chance'=>1,
        'desireable'=>false,
        'action'=>['hp'=>-200]
    ],
    'gold'=>[
        'gfx'=>'gold.png',
        'entity_id'=>'303',
        'desc'=>'some gold',
        'chance'=>4,
        'desireable'=>null,
        'action'=>['inventory'=>'1']
    ],
    'xp'=>[
        'gfx'=>'xp.png',
        'entity_id'=>'304',
        'desc'=>'A book of knowledge',
        'chance'=>3,
        'desireable'=>true,
        'action'=>['xp'=>'150']
    ]
    
    
];