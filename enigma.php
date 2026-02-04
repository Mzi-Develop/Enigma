<?php

require_once 'enigma-core.php';
require_once 'enigma-menu.php';

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function main() {
    try {
        $menu = new EnigmaMenu();
        
        $menu->showWelcome();
        
        while (true) {
            $choice = $menu->showMainMenu();
            
            switch ($choice) {
                case 1: 
                    $menu->selectEnigmaVersion();
                    break;
                    
                case 2:
                    $menu->configureRotors();
                    break;
                    
                case 3: 
                    $menu->configurePlugboard();
                    break;
                    
                case 4:
                    $menu->processText();
                    break;
                    
                case 5: 
                    $menu->showCurrentSettings();
                    break;
                    
                case 6: 
                    $menu->resetSettings();
                    break;
                    
                case 7: 
                    $menu->showHelp();
                    break;
                    
                case 8: 
                    $menu->showExitMessage();
                    exit(0);
                    
                default:
                    echo "Неверный выбор. Попробуйте снова.\n";
                    sleep(1);
            }
        }
        
    } catch (Exception $e) {
        echo "\nОшибка: " . $e->getMessage() . "\n";
        echo "Нажмите Enter для продолжения...";
        fgets(STDIN);
        main(); 
    }
}

main();