<?php 
session_start();
class Character {
    public $name;
    public $role;
    public $health;
    public $strength;
    public $stamina;
    public $intelligence;
    public $spriteImage;
    public $spriteClass;
    public $spriteOffsetX;
    public $spriteOffsetY;
    public $spriteFrameSize;
    public $stats;
    public $attacks;

    public function __construct($name, $role, $health, $strength, $stamina, $intelligence, $spriteImage, $spriteClass, $spriteOffsetX, $spriteOffsetY, $spriteFrameSize, array $attacks)
    {
        $this->name = $name;
        $this->role = $role;
        $this->health = $health;
        $this->strength = $strength;
        $this->stamina = $stamina;
        $this->intelligence = $intelligence;
        $this->spriteImage = $spriteImage;
        $this->spriteClass = $spriteClass;
        $this->spriteOffsetX = $spriteOffsetX;
        $this->spriteOffsetY = $spriteOffsetY;
        $this->spriteFrameSize = $spriteFrameSize;
        $this->stats = [
            'hp' => $health,
            'intelligence' => $intelligence,
            'strength' => $strength,
            'stamina' => $stamina,
        ];
        $this->attacks = $attacks;
    }
}











$characters = [
    'mage' => new Character(
        'Mage',
        'Arcane Caster',
        80,
        5,
        70,
        9,
        'Mage.png',
        'mage-idle',
        '0px',
        '0px',
        64,
        [
            ['name' => 'Fireball', 'icon' => 'fireball.png', 'description' => 'Launch a blazing fireball that scorches enemies from range.'],
            ['name' => 'Physical Attack', 'icon' => 'Strength.png', 'description' => 'Close physical attack using raw strength and quick strikes.']
        ]
    ),
    'knight' => new Character(
        'Knight',
        'Heavy Warrior',
        90,
        8,
        80,
        6,
        'knight.png',
        'knight-idle',
        '-100px',
        '-96px',
        32,
        [
            ['name' => 'Sword Swing', 'icon' => 'SwordSwing.png', 'description' => 'A sweeping sword strike that can cleave through armor.'],
            ['name' => 'Leg Kick', 'icon' => 'LegKick.png', 'description' => 'A low, powerful kick that stuns enemies briefly.']
        ]
    ),
    'traveler' => new Character(
        'Traveler',
        'Wandering Scout',
        70,
        6,
        100,
        7,
        'traveler.png',
        'traveler-idle',
        '0px',
        '0px',
        64,
        [
            ['name' => 'Map Strike', 'icon' => 'map.png', 'description' => 'A swift strike using a curled map to confuse enemies.'],
            ['name' => 'Torch Jab', 'icon' => 'torch.png', 'description' => 'A bright jab with a torch that deals burning damage.']
        ]
    ),
];

?>
