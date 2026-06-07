<?php 
session_start();

class Enemy {
    public $name;
    public $health;
    public $maxHealth;
    public $strength;
    public $stamina;
    public $stats;
    public $idleAnimation;
    public $attackAnimation;
    public $dieAnimation;
    public $hitAnimation;
    public $runAnimation;
    public $stunnedAnimation;
    public $type;
    
    public function __construct($type, $name, $health, $strength, $stamina, $stats, $idleAnimation, $attackAnimation, $dieAnimation, $hitAnimation, $runAnimation, $stunnedAnimation) {
        $this->type = $type;
        $this->name = $name;
        $this->health = $health;
        $this->maxHealth = $health;
        $this->strength = $strength;
        $this->stamina = $stamina;
        $this->stats = $stats;
        $this->idleAnimation = $idleAnimation;
        $this->attackAnimation = $attackAnimation;
        $this->dieAnimation = $dieAnimation;
        $this->hitAnimation = $hitAnimation;
        $this->runAnimation = $runAnimation;
        $this->stunnedAnimation = $stunnedAnimation;
    }
}

function getEnemyByType($type) {
    $enemies = [
        'mushroom' => new Enemy(
            'mushroom',
            'Mushroom',
            50,
            3,
            20,
            ['hp' => 50, 'strength' => 3, 'stamina' => 20, 'intelligence' => 2],
            'Mushroom/Mushroom-Idle.png',
            'Mushroom/Mushroom-Attack.png',
            'Mushroom/Mushroom-Die.png',
            'Mushroom/Mushroom-Hit.png',
            'Mushroom/Mushroom-Run.png',
            'Mushroom/Mushroom-Stun.png'
        )
    ];
    
    return $enemies[$type] ?? null;
}

function getRandomEnemyForLocation($location) {
    if ($location === 'forest') {
        return getEnemyByType('mushroom');
    } else if ($location === 'city') {
        return getEnemyByType('mushroom');
    }
    return getEnemyByType('mushroom');
}
?>