<?php

$config['MYSQL']['HOST']	=	"kayako.example.com";
$config['MYSQL']['USER']	=	"john_doe";
$config['MYSQL']['PWD']		=	"secret_pass";
$config['MYSQL']['DBNAME']		=	"kayako_db";

# ID from kayako db
$config['enabledID'] = "6,7,10,12,19,24";

$config['MYSQLas']['HOST']="asterisk.example.com";
$config['MYSQLas']['USER']="john_doe";
$config['MYSQLas']['PWD']="other_secret_pass";
$config['MYSQLas']['DBNAME']="asteriskcdrdb";


# ID from kayako DB => SIP number
$sip = array(
    "6" => "209",
    "7" => "223",
    "10" => "203",
    "12" => "252",
    "19" => "254",
    "24" => "253"
);
