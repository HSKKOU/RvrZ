<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ItemSimElemModelTable
{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll()
  {
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }

  public function getItemSimElem($item1, $item2)
  {
    $rowSet = $this->tableGateway->select(array('item1_code' => $item1, 'item2_code' => $item2));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find row $item1, $item2");
    }

    return $row;
  }

  public function saveItemSimElem(ItemSimElemModel $itemSimElemModel)
  {
    $data = array(
      'item1_code' => $itemSimElemModel->item1_code,
      'item2_code' => $itemSimElemModel->item2_code,
      'i1Sum' => $itemSimElemModel->i1Sum,
      'i2Sum' => $itemSimElemModel->i2Sum,
      'i1P2Sum' => $itemSimElemModel->i1P2Sum,
      'i2P2Sum' => $itemSimElemModel->i2P2Sum,
      'i1xi2Sum' => $itemSimElemModel->i1xi2Sum,
      'num' => $itemSimElemModel->num,
    );

    $item1 = $itemSimElemModel->item1_code;
    $item2 = $itemSimElemModel->item2_code;
    if ($item1 == '' || $item2 == '') {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getItemSimElem($item1, $item2)) {
        $this->tableGateway->update($data, array('item1_code' => $item1, 'item2_code' => $item2));
      } else {
        return new \Exception("ItemSimElemModel item1/2 dose not exist");
      }
    }
  }

  public function deleteItemSimElem($item1, $item2)
  {
    return $this->tableGateway->delete(array('item1_code' => $item1, 'item2_code' => $item2));
  }


  public function getLastData()
  {
    return $this->tableGateway->lastInsertValue;
  }
}
