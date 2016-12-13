<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\DisplayItemModel;

class DisplayItemRestfulController extends AbstractRvrController
{
  protected $displayItemTable;

  public function getDisplayItemTable()
  {
    if(!$this->displayItemTable)
    {
      $sm = $this->getServiceLocator();
      $this->displayItemTable = $sm->get('Application\Model\DisplayItemModelTable');
    }

    return $this->displayItemTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
    $gotModel = $this->getDisplayItemTable()->getDisplayItem($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'user_id' => $gotModel->user_id,
      'item_id' => $gotModel->item_id,
    ));
  }

  public function create($data)
  {
    $newModel = new DisplayItemModel();
    $newModel->exchangeArray($data);

    $result = $this->getDisplayItemTable()->saveDisplayItem($newModel);
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

    $rowSet = $this->getDisplayItemTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'user_id' => $row->user_id,
        'item_id' => $row->item_id,
      );
    }

    return $data;
  }
}
