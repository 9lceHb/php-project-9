<?php

namespace Hexlet\Code\Apptest;

use PHPUnit\Framework\TestCase;

use function Hexlet\Code\App\abba;

// класс UtilsTest наследует класс TestCase
// имя класса совпадает с именем файла
class AppTest extends TestCase
{
    public function testabba(): void
    {
        $testAnswer1 = 5;
        $this->assertEquals($testAnswer1, abba(5));
    }
}
