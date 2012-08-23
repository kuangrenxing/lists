<?php
require_once('./common/define.php');
require_once('./common/config.php');

Globals::requireClass('Controller');
Controller::runController(null, $config);
