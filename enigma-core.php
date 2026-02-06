<?php

class EnigmaMachine {
    private $version;
    private $rotors = [];
    private $reflector;
    private $plugboard = [];
    private $rotorPositions = [];
    private $initialRotorPositions = [];
    private $lastEncryptionPositions = [];
    
    private $versions = [
        'Enigma I' => [
            'description' => 'Enigma Wehrmacht (1930)',
            'rotors' => ['I', 'II', 'III', 'IV', 'V'],
            'reflectors' => ['UKW-B', 'UKW-C']
        ],
        'Enigma M3' => [
            'description' => 'Enigma Luftwaffe (1938)',
            'rotors' => ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'],
            'reflectors' => ['UKW-B', 'UKW-C']
        ],
        'Enigma M4' => [
            'description' => 'Enigma Kriegsmarine (1942)',
            'rotors' => ['Beta', 'Gamma', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'],
            'reflectors' => ['UKW-B', 'UKW-C']
        ],
        'Enigma T' => [
            'description' => 'Enigma Tirpitz (1942)',
            'rotors' => ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'],
            'reflectors' => ['UKW-T']
        ]
    ];
    
    private $rotorWiring = [
        'I'    => 'EKMFLGDQVZNTOWYHXUSPAIBRCJ',
        'II'   => 'AJDKSIRUXBLHWTMCQGZNPYFVOE',
        'III'  => 'BDFHJLCPRTXVZNYEIWGAKMUSQO',
        'IV'   => 'ESOVPZJAYQUIRHXLNFTGKDCMWB',
        'V'    => 'VZBRGITYUPSDNHLXAWMJQOFECK',
        'VI'   => 'JPGVOUMFYQBENHZRDKASXLICTW',
        'VII'  => 'NZJHGRCXMYSWBOUFAIVLPEKQDT',
        'VIII' => 'FKQHTLXOCBJSPDZRAMEWNIUYGV',
        'Beta' => 'LEYJVCNIXWPBQMDRTAKZGFUHOS',
        'Gamma' => 'FSOKANUERHMBTIYCWLQPZXVGJD'
    ];
    
    private $reflectorWiring = [
        'UKW-B' => 'YRUHQSLDPXNGOKMIEBFZCWVJAT',
        'UKW-C' => 'FVPJIAOYEDRZXWGCTKUQSBNMHL',
        'UKW-T' => 'GEKPBTAUMOCNILJDXZYFHWVQSR'
    ];
    
    private $rotorNotches = [
        'I'    => ['Q'],
        'II'   => ['E'],
        'III'  => ['V'],
        'IV'   => ['J'],
        'V'    => ['Z'],
        'VI'   => ['Z', 'M'],
        'VII'  => ['Z', 'M'],
        'VIII' => ['Z', 'M']
    ];
    
    private $settingsFile = 'enigma_settings.json';
    
    public function __construct($version = 'Enigma I') {
        $this->setVersion($version);
        $this->reset();
        $this->loadSettings();
    }
    
    public function saveSettings() {
        $settings = [
            'version' => $this->version,
            'rotors' => $this->rotors,
            'reflector' => $this->reflector,
            'plugboard' => $this->plugboard,
            'rotorPositions' => $this->rotorPositions,
            'initialRotorPositions' => $this->initialRotorPositions,
            'lastEncryptionPositions' => $this->lastEncryptionPositions 
        ];
        
        file_put_contents($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    }
    
    public function loadSettings() {
        if (file_exists($this->settingsFile)) {
            try {
                $json = file_get_contents($this->settingsFile);
                $settings = json_decode($json, true);
                
                if ($settings) {
                    $this->version = $settings['version'];
                    $this->rotors = $settings['rotors'];
                    $this->reflector = $settings['reflector'];
                    $this->plugboard = $settings['plugboard'];
                    $this->rotorPositions = $settings['rotorPositions'];
                    $this->initialRotorPositions = $settings['initialRotorPositions'];
                    $this->lastEncryptionPositions = isset($settings['lastEncryptionPositions']) ? $settings['lastEncryptionPositions'] : $settings['initialRotorPositions'];
                }
            } catch (Exception $e) {
                $this->reset();
            }
        }
    }
    
    public function deleteSettings() {
        if (file_exists($this->settingsFile)) {
            unlink($this->settingsFile);
        }
    }
    
    public function setVersion($version) {
        if (!isset($this->versions[$version])) {
            throw new Exception("Неизвестная версия Энигмы: $version");
        }
        $this->version = $version;
    }
    
    public function getAvailableVersions() {
        return $this->versions;
    }
    
    public function getCurrentVersion() {
        return $this->version;
    }
    
    public function reset() {
        $config = $this->versions[$this->version];
        
        if ($this->version == 'Enigma M4') {
            $this->rotors = ['Beta', 'I', 'II', 'III'];
            $this->rotorPositions = [0, 0, 0, 0];
        } else {
            $this->rotors = array_slice($config['rotors'], 0, 3);
            $this->rotorPositions = [0, 0, 0];
        }
        
        $this->initialRotorPositions = $this->rotorPositions;
        $this->lastEncryptionPositions = $this->rotorPositions;
        
        $this->reflector = $config['reflectors'][0];
        
        $this->plugboard = [];
    }
    
    public function resetToInitialPositions() {
        $this->rotorPositions = $this->initialRotorPositions;
    }
    
    public function resetToLastEncryptionPositions() {
        $this->rotorPositions = $this->lastEncryptionPositions;
    }
    
    public function saveCurrentPositionsAsInitial() {
        $this->initialRotorPositions = $this->rotorPositions;
    }
    
    public function saveCurrentPositionsAsLastEncryption() {
        $this->lastEncryptionPositions = $this->rotorPositions;
    }
    
    public function setRotors($rotors, $positions = null) {
        $config = $this->versions[$this->version];
        
        $expectedCount = ($this->version == 'Enigma M4') ? 4 : 3;
        if (count($rotors) != $expectedCount) {
            throw new Exception("Для $this->version требуется $expectedCount ротора");
        }
        
        foreach ($rotors as $rotor) {
            if (!in_array($rotor, $config['rotors'])) {
                throw new Exception("Неверный ротор: $rotor для версии $this->version");
            }
        }
        
        $this->rotors = $rotors;
        
        if ($positions) {
            if (count($positions) != count($rotors)) {
                throw new Exception("Количество позиций должно совпадать с количеством роторов");
            }
            $this->rotorPositions = $positions;
            $this->initialRotorPositions = $positions;
            $this->lastEncryptionPositions = $positions;
        } else {
            $this->rotorPositions = array_fill(0, count($rotors), 0);
            $this->initialRotorPositions = $this->rotorPositions;
            $this->lastEncryptionPositions = $this->rotorPositions;
        }
    }
    
    public function setRotorPositionsOnly($positions) {
        $expectedCount = ($this->version == 'Enigma M4') ? 4 : 3;
        if (count($positions) != $expectedCount) {
            throw new Exception("Для $this->version требуется $expectedCount позиций");
        }
        $this->rotorPositions = $positions;
        $this->initialRotorPositions = $positions;
        $this->lastEncryptionPositions = $positions;
    }
    
    public function setReflector($reflector) {
        $config = $this->versions[$this->version];
        
        if (!in_array($reflector, $config['reflectors'])) {
            throw new Exception("Неверный рефлектор: $reflector для версии $this->version");
        }
        
        $this->reflector = $reflector;
    }
    
    public function setPlugboard($connections) {
        $this->plugboard = [];
        
        foreach ($connections as $connection) {
            if (strlen($connection) != 2) {
                throw new Exception("Неверное соединение: $connection");
            }
            
            $a = strtoupper($connection[0]);
            $b = strtoupper($connection[1]);
            
            if ($a == $b) {
                throw new Exception("Буква не может быть соединена сама с собой: $a");
            }
            
            if (isset($this->plugboard[$a]) || isset($this->plugboard[$b])) {
                throw new Exception("Буква уже используется в соединении: $a-$b");
            }
            
            $this->plugboard[$a] = $b;
            $this->plugboard[$b] = $a;
        }
    }
    
    public function encrypt($text) {
        $result = '';
        $text = preg_replace('/[^A-Za-z]/', '', strtoupper($text));
        
        $this->lastEncryptionPositions = $this->rotorPositions;
        
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $result .= $this->processChar($char);
        }
        
        $this->saveSettings();
        return $result;
    }
    
    public function decrypt($text) {
        $this->rotorPositions = $this->lastEncryptionPositions;
        return $this->encrypt($text);
    }
    
    public function decryptWithInitialPositions($text) {
        $this->rotorPositions = $this->initialRotorPositions;
        return $this->encrypt($text);
    }
    
    private function processChar($char) {
        $this->rotateRotors();
        
        if (isset($this->plugboard[$char])) {
            $char = $this->plugboard[$char];
        }
        
        for ($i = count($this->rotors) - 1; $i >= 0; $i--) {
            $char = $this->passThroughRotor($char, $this->rotors[$i], $this->rotorPositions[$i], false);
        }
        
        $char = $this->passThroughReflector($char, $this->reflector);
        
        for ($i = 0; $i < count($this->rotors); $i++) {
            $char = $this->passThroughRotor($char, $this->rotors[$i], $this->rotorPositions[$i], true);
        }
        
        if (isset($this->plugboard[$char])) {
            $char = $this->plugboard[$char];
        }
        
        return $char;
    }
    
    private function rotateRotors() {
        $rotateNext = true;
        
        for ($i = 0; $i < count($this->rotors); $i++) {
            if ($rotateNext) {
                $this->rotorPositions[$i] = ($this->rotorPositions[$i] + 1) % 26;
                
                $rotateNext = false;
                if (isset($this->rotorNotches[$this->rotors[$i]])) {
                    $currentPos = chr(65 + $this->rotorPositions[$i]);
                    if (in_array($currentPos, $this->rotorNotches[$this->rotors[$i]])) {
                        $rotateNext = true;
                    }
                }
            }
        }
    }
    
    private function passThroughRotor($char, $rotorType, $position, $reverse) {
        $wiring = $this->rotorWiring[$rotorType];
        $charIndex = ord($char) - 65;
        
        if ($reverse) {
            $pos = ($charIndex + $position) % 26;
            $char = chr(65 + $pos);
            $index = strpos($wiring, $char);
            $resultIndex = ($index - $position + 26) % 26;
        } else {
            $pos = ($charIndex + $position) % 26;
            $char = chr(65 + $pos);
            $resultChar = $wiring[ord($char) - 65];
            $resultIndex = (ord($resultChar) - 65 - $position + 26) % 26;
        }
        
        return chr(65 + $resultIndex);
    }
    
    private function passThroughReflector($char, $reflectorType) {
        $wiring = $this->reflectorWiring[$reflectorType];
        $index = ord($char) - 65;
        return $wiring[$index];
    }
    
    public function getCurrentSettings() {
        $rotorPositionsStr = '';
        foreach ($this->rotorPositions as $pos) {
            $rotorPositionsStr .= chr(65 + $pos) . ' ';
        }
        
        $initialPositionsStr = '';
        foreach ($this->initialRotorPositions as $pos) {
            $initialPositionsStr .= chr(65 + $pos) . ' ';
        }
        
        $lastEncryptionPositionsStr = '';
        foreach ($this->lastEncryptionPositions as $pos) {
            $lastEncryptionPositionsStr .= chr(65 + $pos) . ' ';
        }
        
        $plugboardStr = '';
        $used = [];
        foreach ($this->plugboard as $a => $b) {
            if (!in_array($a, $used) && !in_array($b, $used)) {
                $plugboardStr .= "$a-$b ";
                $used[] = $a;
                $used[] = $b;
            }
        }
        
        return [
            'Версия' => $this->version,
            'Описание' => $this->versions[$this->version]['description'],
            'Роторы' => implode(' | ', $this->rotors),
            'Текущие позиции' => trim($rotorPositionsStr),
            'Начальные позиции' => trim($initialPositionsStr),
            'Позиции последнего шифрования' => trim($lastEncryptionPositionsStr),
            'Рефлектор' => $this->reflector,
            'Коммутационная панель' => $plugboardStr ?: 'Нет соединений'
        ];
    }
    
    public function getRotorPositions() {
        $positions = [];
        foreach ($this->rotorPositions as $pos) {
            $positions[] = chr(65 + $pos);
        }
        return implode(' ', $positions);
    }
    
    public function getInitialRotorPositions() {
        $positions = [];
        foreach ($this->initialRotorPositions as $pos) {
            $positions[] = chr(65 + $pos);
        }
        return implode(' ', $positions);
    }
    
    public function getLastEncryptionPositions() {
        $positions = [];
        foreach ($this->lastEncryptionPositions as $pos) {
            $positions[] = chr(65 + $pos);
        }
        return implode(' ', $positions);
    }
}