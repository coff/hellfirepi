#!/usr/bin/php
<?php

function setup() {
    $sensor1 = new \Hellfire\Sensor\TemperatureSensor('Sensor1', '/sys/bus/w1/devices/28-0000084a49a8/w1_slave');

}

function loop() {
    while (true) {

    }
}

loop();
