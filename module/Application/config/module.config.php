<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
              'type' => 'Zend\Mvc\Router\Http\Literal',
              'options' => array(
                'route'    => '/',
                'defaults' => array(
                  '__NAMESPACE__' => 'Application\Controller',
                ),
              ),
              'may_terminate' => true,
              'child_routes' => array(
                'default' => array(
                  'type'    => 'Segment',
                  'options' => array(
                    'route'    => '[:controller][/:user_id][/]',
                    'constraints' => array(
                      'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                      'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                      'controller' => 'Index',
                      'action'     => 'index',
                    ),
                  ),
                ),
              ),
            ),

            'app' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/app',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/:controller[/:id][/]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                              'action' => null,
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Rvr01' => Controller\Rvr01Controller::class,
            'Application\Controller\Rvr02' => Controller\Rvr02Controller::class,
            'Application\Controller\Import' => Controller\ImportController::class,
            'Application\Controller\Analysis' => Controller\AnalysisController::class,

            'Application\Controller\Index' => Controller\IndexController::class,
            'Application\Controller\Test' => Controller\TestRestfulController::class,
            'Application\Controller\Item' => Controller\ItemRestfulController::class,
            'Application\Controller\ItemGenre' => Controller\ItemGenreRestfulController::class,
            'Application\Controller\Review' => Controller\ReviewRestfulController::class,
            'Application\Controller\Inputs' => Controller\InputsRestfulController::class,
            'Application\Controller\Rvr' => Controller\RvrRestfulController::class,
            'Application\Controller\User' => Controller\UserRestfulController::class,
            'Application\Controller\Rep' => Controller\ReputationRestfulController::class,
            'Application\Controller\ReviewUser' => Controller\ReviewUserRestfulController::class,
            'Application\Controller\ItemSimElem' => Controller\ItemSimElemRestfulController::class,
            'Application\Controller\DisplayItem' => Controller\DisplayItemRestfulController::class,
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'app/index/index'         => __DIR__ . '/../view/application/index/index.phtml',
            'application/rvr01/index'         => __DIR__ . '/../view/application/index/rvr01.phtml',
            'application/rvr02/index'         => __DIR__ . '/../view/application/index/rvr02.phtml',
            'application/import/index'         => __DIR__ . '/../view/application/index/import.phtml',
            'application/analysis/index'         => __DIR__ . '/../view/application/index/analysis.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
          'ViewJsonStrategy'
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
