<?php

return [
    'health'=>[
        'gfx'=>'heart.png',
        'char'=>'H',
        'desc'=>'Heal up!',
        'chance'=>5,
        'desireable'=>true,
        'action'=>['maxhp'=>50,'hp'=>300]
    ],
    'spikes'=>[
        'gfx'=>'spikes.png',
        'char'=>'S',
        'desc'=>'Spike trap. Bad to step on.',
        'chance'=>1,
        'desireable'=>false,
        'action'=>['hp'=>-200]
    ],
    'gold'=>[
        'gfx'=>'gold.png',
        'char'=>'G',
        'desc'=>'some gold',
        'chance'=>4,
        'desireable'=>null,
        'action'=>['inventory'=>'1']
    ],
    'xp'=>[
        'gfx'=>'xp.png',
        'char'=>'x',
        'desc'=>'A book of knowledge',
        'chance'=>3,
        'desireable'=>true,
        'action'=>['xp'=>'150']
    ]
    
    
];