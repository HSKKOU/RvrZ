<?php
namespace Application\Controller;

require 'Zend\Controller\Action.php';
require 'Zend\Controller\Plugin\ErrorHandler.php'

class ErrorController extends Zend_Controller_Action
{
  public function errorAction()
  {
    $errors = $this->_getParam('error_handler');

    switch ($error->type) {
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
      case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        $this->view->msg = 'Not found Controller or Action';
        break;
      default:
        $this->view->msg = $error->exception->getMessage();
        break;
    }
  }
}
