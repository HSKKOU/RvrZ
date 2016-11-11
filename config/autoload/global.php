<?php

return array(
  'db' => array(
    'driver' => 'Pdo',
    'dsn' => 'mysql:host=127.0.0.1;dbname=vrshopping;charset=utf8;',
  ),
  'service_manager' => array(
    'factories' => array(
      'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
    ),
  ),
);
