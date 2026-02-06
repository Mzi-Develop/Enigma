<?php

class EnigmaMenu {
    private $enigma;
    
    public function __construct() {
        $this->enigma = new EnigmaMachine('Enigma I');
    }
    
    public function showWelcome() {
        system('cls');
        echo "==================================================\n";
        echo "         ЭНИГМА - ШИФРОВАЛЬНАЯ МАШИНА\n";
        echo "                 СИМУЛЯТОР v1.0\n";
        echo "==================================================\n\n";
        echo "Историческая справка:\n";
        echo "Энигма — роторная шифровальная машина, использовавшаяся\n";
        echo "для шифрования и дешифрования секретных сообщений.\n";
        echo "Наиболее известна своей службой в нацистской Германии\n";
        echo "во время Второй мировой войны.\n\n";
        echo "ВАЖНО: Настройки автоматически сохраняются при выходе.\n";
        echo "       Для корректной расшифровки используйте те же\n";
        echo "       начальные позиции роторов, что и при шифровании!\n\n";
        echo "Нажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
    public function showMainMenu() {
        system('cls');
        echo "==================================================\n";
        echo "               ГЛАВНОЕ МЕНЮ ЭНИГМЫ\n";
        echo "==================================================\n";
        echo "Текущая версия: " . $this->enigma->getCurrentVersion() . "\n";
        echo "Настройки автоматически сохраняются\n";
        echo "==================================================\n";
        echo "1. Выбрать версию Энигмы\n";
        echo "2. Настроить роторы и их позиции\n";
        echo "3. Настроить коммутационную панель\n";
        echo "4. Шифровать/дешифровать текст\n";
        echo "5. Показать текущие настройки\n";
        echo "6. Сбросить настройки\n";
        echo "7. Справка\n";
        echo "8. Выход и сохранение\n";
        echo "==================================================\n";
        echo "Выберите действие (1-8): ";
        
        $choice = trim(fgets(STDIN));
        return intval($choice);
    }
    
    public function selectEnigmaVersion() {
        system('cls');
        echo "==================================================\n";
        echo "             ВЫБОР ВЕРСИИ ЭНИГМЫ\n";
        echo "==================================================\n\n";
        
        $versions = $this->enigma->getAvailableVersions();
        $i = 1;
        foreach ($versions as $name => $config) {
            echo "$i. $name\n";
            echo "   {$config['description']}\n";
            echo "   Роторы: " . implode(', ', $config['rotors']) . "\n";
            echo "   Рефлекторы: " . implode(', ', $config['reflectors']) . "\n\n";
            $i++;
        }
        
        echo "==================================================\n";
        echo "Текущая версия: " . $this->enigma->getCurrentVersion() . "\n";
        echo "Выберите версию (1-" . count($versions) . "): ";
        
        $choice = intval(trim(fgets(STDIN)));
        $versionNames = array_keys($versions);
        
        if ($choice >= 1 && $choice <= count($versions)) {
            $selectedVersion = $versionNames[$choice - 1];
            $this->enigma->setVersion($selectedVersion);
            $this->enigma->reset();
            echo "\nВерсия изменена на: $selectedVersion\n";
            echo "Настройки сброшены к значениям по умолчанию.\n";
            
            $this->enigma->saveSettings();
        } else {
            echo "\nНеверный выбор!\n";
        }
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
    public function configureRotors() {
        system('cls');
        echo "==================================================\n";
        echo "          НАСТРОЙКА РОТОРОВ И ПОЗИЦИЙ\n";
        echo "==================================================\n\n";
        
        $version = $this->enigma->getCurrentVersion();
        $versions = $this->enigma->getAvailableVersions();
        $availableRotors = $versions[$version]['rotors'];
        
        echo "Доступные роторы для $version:\n";
        echo implode(', ', $availableRotors) . "\n\n";
        
        $rotorCount = ($version == 'Enigma M4') ? 4 : 3;
        $rotors = [];
        $positions = [];
        
        for ($i = 1; $i <= $rotorCount; $i++) {
            echo "Ротор #$i (введите название): ";
            $rotor = trim(fgets(STDIN));
            
            if (!in_array($rotor, $availableRotors)) {
                echo "Неверный ротор! Используется ротор по умолчанию.\n";
                $rotor = $availableRotors[0];
            }
            
            $rotors[] = $rotor;
            
            echo "Начальная позиция ротора #$i (A-Z): ";
            $pos = strtoupper(trim(fgets(STDIN)));
            if (strlen($pos) != 1 || !ctype_alpha($pos)) {
                echo "Неверная позиция! Используется A.\n";
                $pos = 'A';
            }
            $positions[] = ord($pos) - 65;
            
            echo "\n";
        }
        
        try {
            $this->enigma->setRotors($rotors, $positions);
            echo "Роторы успешно настроены!\n";
            echo "Начальные позиции сохранены: " . $this->enigma->getInitialRotorPositions() . "\n";
            
            $this->enigma->saveSettings();
        } catch (Exception $e) {
            echo "Ошибка: " . $e->getMessage() . "\n";
        }
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
    public function configurePlugboard() {
        system('cls');
        echo "==================================================\n";
        echo "        НАСТРОЙКА КОММУТАЦИОННОЙ ПАНЕЛИ\n";
        echo "==================================================\n\n";
        
        echo "Коммутационная панель позволяет менять местами пары букв\n";
        echo "перед шифрованием и после.\n\n";
        
        echo "Сколько соединений вы хотите установить? (0-10): ";
        $numConnections = intval(trim(fgets(STDIN)));
        
        if ($numConnections < 0 || $numConnections > 10) {
            echo "Неверное количество! Допустимо 0-10 соединений.\n";
            echo "\nНажмите Enter для продолжения...";
            fgets(STDIN);
            return;
        }
        
        $connections = [];
        
        for ($i = 1; $i <= $numConnections; $i++) {
            echo "Соединение #$i (например, AB): ";
            $conn = strtoupper(trim(fgets(STDIN)));
            
            if (strlen($conn) != 2 || !ctype_alpha($conn)) {
                echo "Неверный формат! Используйте две буквы (например, AB)\n";
                $i--;
                continue;
            }
            
            $connections[] = $conn;
        }
        
        try {
            $this->enigma->setPlugboard($connections);
            echo "\nКоммутационная панель успешно настроена!\n";
            
            $this->enigma->saveSettings();
        } catch (Exception $e) {
            echo "\nОшибка: " . $e->getMessage() . "\n";
        }
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
public function processText() {
    system('cls');
    echo "==================================================\n";
    echo "          ШИФРОВАНИЕ / ДЕШИФРОВАНИЕ\n";
    echo "==================================================\n\n";
    
    echo "Текущие позиции роторов: " . $this->enigma->getRotorPositions() . "\n";
    echo "Начальные позиции роторов: " . $this->enigma->getInitialRotorPositions() . "\n";
    echo "Позиции последнего шифрования: " . $this->enigma->getLastEncryptionPositions() . "\n\n";
    
    echo "Введите текст для обработки (только буквы A-Z):\n";
    $text = trim(fgets(STDIN));
    
    if (empty($text)) {
        echo "Текст не может быть пустым!\n";
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
        return;
    }
    
    echo "\nВыберите действие:\n";
    echo "1. Зашифровать - использовать текущие позиции\n";
    echo "2. Зашифровать - использовать начальные позиции\n";
    echo "3. Расшифровать использовать позиции последнего шифрования\n";
    echo "4. Расшифровать - использовать начальные позиции\n";
    echo "5. Установить - текущие позиции как начальные\n";
    echo "6. Установить - текущие позиции как позиции шифрования\n";
    echo "Выбор (1-6): ";
    
    $choice = intval(trim(fgets(STDIN)));
    
    $result = '';
    if ($choice == 1) {
        $result = $this->enigma->encrypt($text);
        echo "\nШифрование выполнено с текущих позиций!\n";
        echo "Позиции сохранены для расшифровки: " . $this->enigma->getLastEncryptionPositions() . "\n";
    } else if ($choice == 2) {
        $this->enigma->resetToInitialPositions();
        $result = $this->enigma->encrypt($text);
        echo "\nШифрование выполнено с начальных позиций!\n";
        echo "Позиции сохранены для расшифровки: " . $this->enigma->getLastEncryptionPositions() . "\n";
    } else if ($choice == 3) {
        $this->enigma->resetToLastEncryptionPositions();
        $result = $this->enigma->decrypt($text);
        echo "\nДешифрование выполнено с позиций последнего шифрования!\n";
        echo "Использованы позиции: " . $this->enigma->getLastEncryptionPositions() . "\n";
    } else if ($choice == 4) {
        $this->enigma->resetToInitialPositions();
        $result = $this->enigma->decryptWithInitialPositions($text);
        echo "\nДешифрование выполнено с начальных позиций!\n";
        echo "Использованы позиции: " . $this->enigma->getInitialRotorPositions() . "\n";
    } else if ($choice == 5) {
        $this->enigma->saveCurrentPositionsAsInitial();
        echo "\nТекущие позиции сохранены как начальные: " . $this->enigma->getInitialRotorPositions() . "\n";
        
        $this->enigma->saveSettings();
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
        return;
    } else if ($choice == 6) {
        $this->enigma->saveCurrentPositionsAsLastEncryption();
        echo "\nТекущие позиции сохранены как позиции шифрования: " . $this->enigma->getLastEncryptionPositions() . "\n";
        
        $this->enigma->saveSettings();
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
        return;
    } else {
        echo "Неверный выбор!\n";
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
        return;
    }
    
    echo "\n==================================================\n";
    echo "РЕЗУЛЬТАТ:\n";
    echo "==================================================\n";
    echo "Исходный текст: $text\n";
    echo "Обработанный текст: $result\n";
    
    $grouped = '';
    for ($i = 0; $i < strlen($result); $i++) {
        $grouped .= $result[$i];
        if (($i + 1) % 5 == 0 && ($i + 1) < strlen($result)) {
            $grouped .= ' ';
        }
    }
    echo "Группированный: $grouped\n";
    echo "Текущие позиции роторов: " . $this->enigma->getRotorPositions() . "\n";
    echo "Позиции для расшифровки: " . $this->enigma->getLastEncryptionPositions() . "\n";
    echo "==================================================\n";
    
    echo "\nНажмите Enter для продолжения...";
    fgets(STDIN);
}
    
    public function showCurrentSettings() {
        system('cls');
        echo "==================================================\n";
        echo "          ТЕКУЩИЕ НАСТРОЙКИ ЭНИГМЫ\n";
        echo "==================================================\n\n";
        
        $settings = $this->enigma->getCurrentSettings();
        
        foreach ($settings as $key => $value) {
            echo str_pad($key, 25) . ": $value\n";
        }
        
        echo "\n==================================================\n";
        echo "Настройки автоматически сохраняются в файл enigma_settings.json\n";
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
    public function resetSettings() {
        system('cls');
        echo "==================================================\n";
        echo "                СБРОС НАСТРОЕК\n";
        echo "==================================================\n\n";
        
        echo "Вы уверены, что хотите сбросить все настройки?\n";
        echo "Это вернет машину к состоянию по умолчанию и удалит сохраненные настройки.\n";
        echo "Введите 'да' для подтверждения: ";
        
        $confirm = strtolower(trim(fgets(STDIN)));
        
        if ($confirm == 'да' || $confirm == 'yes' || $confirm == 'y') {
            $this->enigma->reset();
            $this->enigma->deleteSettings();
            echo "\nНастройки успешно сброшены!\n";
            echo "Файл с сохраненными настройками удален.\n";
        } else {
            echo "\nСброс отменен.\n";
        }
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
    public function showHelp() {
        system('cls');
        echo "==================================================\n";
        echo "                  СПРАВКА\n";
        echo "==================================================\n\n";
        
        echo "Краткое руководство по использованию Энигмы:\n\n";
        
        echo "1. ВЫБОР ВЕРСИИ ЭНИГМЫ\n";
        echo "   - Enigma I: Стандартная версия Вермахта (3 ротора)\n";
        echo "   - Enigma M3: Военно-морская версия (3 ротора)\n";
        echo "   - Enigma M4: Для подводных лодок (4 ротора)\n";
        echo "   - Enigma T: Японская версия (3 ротора)\n\n";
        
        echo "2. НАСТРОЙКА РОТОРОВ\n";
        echo "   - Выберите роторы из доступных для вашей версии\n";
        echo "   - Установите начальную позицию для каждого ротора (A-Z)\n";
        echo "   - Порядок роторов важен!\n\n";
        
        echo "3. КОММУТАЦИОННАЯ ПАНЕЛЬ\n";
        echo "   - Позволяет менять местами пары букв\n";
        echo "   - Максимум 10 соединений\n";
        echo "   - Формат: AB (меняет местами A и B)\n\n";
        
        echo "4. ШИФРОВАНИЕ\n";
        echo "   - Введите текст (только английские буквы)\n";
        echo "   - Все не-буквенные символы игнорируются\n";
        echo "   - Результат разбивается на группы по 5 символов\n";
        echo "   - ЗАПОМНИТЕ начальные позиции роторов!\n\n";
        
        echo "5. ДЕШИФРОВАНИЕ\n";
        echo "   - Убедитесь, что начальные позиции роторов те же, что при шифровании\n";
        echo "   - Введите зашифрованный текст\n";
        echo "   - Энигма симметрична: шифрование = дешифрование при одинаковых начальных позициях\n\n";
        
        echo "СОХРАНЕНИЕ НАСТРОЕК:\n";
        echo "- Настройки автоматически сохраняются при:\n";
        echo "  * Изменении версии Энигмы\n";
        echo "  * Настройке роторов\n";
        echo "  * Настройке коммутационной панели\n";
        echo "  * Шифровании текста\n";
        echo "  * Выходе из программы\n";
        echo "- Файл настроек: enigma_settings.json\n";
        echo "- При сбросе настроек файл удаляется\n\n";
        
        echo "==================================================\n";
        echo "ИСТОРИЧЕСКАЯ ИНФОРМАЦИЯ:\n";
        echo "Настоящая Энигма имела следующие особенности:\n";
        echo "- 3-4 ротора, выбираемых из набора 5-8 роторов\n";
        echo "- Рефлектор (неподвижный ротор)\n";
        echo "- Коммутационная панель с 6-10 соединениями\n";
        echo "- Ежедневные настройки, меняющиеся по расписанию\n";
        echo "==================================================\n";
        
        echo "\nНажмите Enter для продолжения...";
        fgets(STDIN);
    }
    
    public function showExitMessage() {
        system('cls');
        echo "==================================================\n";
        echo "        СПАСИБО ЗА ИСПОЛЬЗОВАНИЕ ЭНИГМЫ!\n";
        echo "==================================================\n\n";

        $this->enigma->saveSettings();
        
        echo "Настройки сохранены в файл enigma_settings.json\n\n";
        
        echo "Этот симулятор был создан для демонстрации принципов\n";
        echo "работы исторической шифровальной машины Энигма.\n\n";
        
        echo "Интересные факты:\n";
        echo "- Настоящая Энигма была взломана командой Алана Тьюринга\n";
        echo "- Это ускорило конец Второй мировой войны на 2-4 года\n";
        echo "- Всего существовало около 100 000 машин Энигма\n\n";
        
        echo "До свидания!\n";
        sleep(2);
    }
}