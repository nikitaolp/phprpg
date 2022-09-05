<?php
namespace Phprpg;
use Phprpg\Core\{AppStorage};
use Phprpg\Core\State\{Database};

require '../vendor/autoload.php';

AppStorage::set('db', new Database(require 'cred.php'));

echo AppStorage::get('db')->cleanupCron(strtotime("2 days ago"));