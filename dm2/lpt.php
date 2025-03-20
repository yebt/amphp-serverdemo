<?php

require __DIR__ . "/vendor/autoload.php";

$future1 = Amp\async(static function () {
    for ($i = 0; $i < 5; $i++) {
        echo '.';
        Amp\delay(1);
    }
});

$future2 = Amp\async(static function () {
    for ($i = 0; $i < 5; $i++) {
        echo '_';
        Amp\delay(0.5);
    }
});

$future1->await();
$future2->await();
