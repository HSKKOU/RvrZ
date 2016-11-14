<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ReviewUserModel;

class ReviewUserRestfulController extends AbstractRvrController
{
  protected $reviewUserTable;

  public function getReviewUserTable()
  {
    if(!$this->reviewUserTable)
    {
      $sm = $this->getServiceLocator();
      $this->reviewUserTable = $sm->get('Application\Model\ReviewUserModelTable');
    }

    return $this->reviewUserTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($user_name)
  {
    $gotModel = $this->getReviewUserTable()->getReviewUser($user_name);
    return $this->makeSuccessJson(array(
      'user_name' => $gotModel->user_name,
      'reviews' => $gotModel->reviews,
    ));
  }

  public function create($data)
  {
    $newModel = new ReviewUserModel();
    $newModel->exchangeArray($data);
    $result = $this->getReviewUserTable()->saveUser($newModel);
    $savedData = array();
    if ($result == 1) {
      $fetchList = $this->getListRaw();
      $savedData = $fetchList[count($fetchList)-1];
    }

    return $this->makeSuccessJson($savedData);
  }







  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getReviewUserTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'user_name' => $row->user_name,
        'reviews' => $row->reviews,
      );
    }

    return $data;
  }
}
