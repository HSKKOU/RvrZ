<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Application\Model\TestModel;
use Application\Model\TestModelTable;

use Application\Model\ItemModel;
use Application\Model\ItemModelTable;

use Application\Model\ReviewModel;
use Application\Model\ReviewModelTable;

use Application\Model\InputsModel;
use Application\Model\InputsModelTable;

use Application\Model\UserModel;
use Application\Model\UserModelTable;

use Application\Model\ItemMatchModel;
use Application\Model\ItemMatchModelTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig(){
      return array(
        'factories' => array(
          'Application\Model\TestModelTable' => function($sm){
            $tableGateway = $sm->get('TestModelTableGateway');
            $table = new TestModelTable($tableGateway);
            return $table;
          },
          'TestModelTableGateway' => function($sm){
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new TestModel());
            return new TableGateway('test_model', $dbAdapter, null, $resultSetPrototype);
          },

          'Application\Model\ItemModelTable' => function($sm){
            $tableGateway = $sm->get('ItemModelTableGateway');
            $table = new ItemModelTable($tableGateway);
            return $table;
          },
          'ItemModelTableGateway' => function($sm){
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ItemModel());
            return new TableGateway('items', $dbAdapter, null, $resultSetPrototype);
          },

          'Application\Model\ReviewModelTable' => function($sm){
            $tableGateway = $sm->get('ReviewModelTableGateway');
            $table = new ReviewModelTable($tableGateway);
            return $table;
          },
          'ReviewModelTableGateway' => function($sm){
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ReviewModel());
            return new TableGateway('reviews', $dbAdapter, null, $resultSetPrototype);
          },

          'Application\Model\InputsModelTable' => function($sm){
            $tableGateway = $sm->get('InputsModelTableGateway');
            $table = new InputsModelTable($tableGateway);
            return $table;
          },
          'InputsModelTableGateway' => function($sm){
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new InputsModel());
            return new TableGateway('inputs', $dbAdapter, null, $resultSetPrototype);
          },

          'Application\Model\UserModelTable' => function($sm){
            $tableGateway = $sm->get('UserModelTableGateway');
            $table = new UserModelTable($tableGateway);
            return $table;
          },
          'UserModelTableGateway' => function($sm){
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new UserModel());
            return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
          },

          'Application\Model\ItemMatchModelTable' => function($sm){
            $tableGateway = $sm->get('ItemMatchModelTableGateway');
            $table = new ItemMatchModelTable($tableGateway);
            return $table;
          },
          'ItemMatchModelTableGateway' => function($sm){
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ItemMatchModel());
            return new TableGateway('item_match', $dbAdapter, null, $resultSetPrototype);
          },
        )
      );
    }
}
