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

function getEnemyByType($type, $randomHp = true) {
    // Base stats for mushroom
    $baseHealth = 50;
    $baseStrength = 3;
    $baseStamina = 20;
    
    // Random HP between 40 and 60
    if ($randomHp) {
        $health = rand(40, 60);
    } else {
        $health = $baseHealth;
    }
    
    // Strength can also vary slightly based on HP (tougher enemies hit harder)
    $strength = $baseStrength + round(($health - 40) / 10);
    
    $enemies = [
        'mushroom' => new Enemy(
            'mushroom',
            'Mushroom',
            $health,
            $strength,
            $baseStamina,
            ['hp' => $health, 'strength' => $strength, 'stamina' => $baseStamina, 'intelligence' => 1],
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
    // 1/3 chance to encounter enemy, 1/3 nothing, 1/3 lost
    $encounterRoll = rand(1, 3);
    
    if ($encounterRoll == 1) {
        // Encounter enemy
        $enemy = getEnemyByType('mushroom', true);
        return ['type' => 'encounter', 'enemy' => $enemy];
    } elseif ($encounterRoll == 2) {
        // Nothing found
        return ['type' => 'none', 'enemy' => null];
    } else {
        // Lost - returns special state
        return ['type' => 'lost', 'enemy' => null];
    }
}
?>