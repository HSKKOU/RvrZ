<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemSimElemModel;

class ItemSimElemRestfulController extends AbstractRvrController
{
  protected $itemSimElemTable;

  public function getItemSimElemTable()
  {
    if(!$this->itemSimElemTable)
    {
      $sm = $this->getServiceLocator();
      $this->itemSimElemTable = $sm->get('Application\Model\ItemSimElemModelTable');
    }

    return $this->itemSimElemTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($item1_2)
  {
    $itemSp = explode(',', $item1_2);
    $item1 = $itemSp[0];
    $item2 = $itemSp[1];
    $gotModel = $this->getItemSimElemTable()->getItemSimElem($item1, $item2);
    return $this->makeSuccessJson(array(
      'item1_code' => $gotModel->item1_code,
      'item2_code' => $gotModel->item2_code,
      'i1Sum' => $gotModel->i1Sum,
      'i2Sum' => $gotModel->i2Sum,
      'i1P2Sum' => $gotModel->i1P2Sum,
      'i2P2Sum' => $gotModel->i2P2Sum,
      'i1xi2Sum' => $gotModel->i1xi2Sum,
      'num' => $gotModel->num,
    ));
  }

  public function create($data)
  {
    $newModel = new ItemSimElemModel();
    $newModel->exchangeArray($data);
    $result = $this->getItemSimElemTable()->saveUser($newModel);
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

    $rowSet = $this->getItemSimElemTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'item1_code' => $row->item1_code,
        'item2_code' => $row->item2_code,
        'i1Sum' => $row->i1Sum,
        'i2Sum' => $row->i2Sum,
        'i1P2Sum' => $row->i1P2Sum,
        'i2P2Sum' => $row->i2P2Sum,
        'i1xi2Sum' => $row->i1xi2Sum,
        'num' => $row->num,
      );
    }

    return $data;
  }
}
