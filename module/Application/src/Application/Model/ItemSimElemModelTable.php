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
    return $row;
  }

  public function getItemSimElemWithNum2Than()
  {
    $select = $this->tableGateway->getSql()->select();
    $select->where->greaterThan('num', 1);
    $rowSet =  $this->tableGateway->selectWith($select);
    return $rowSet;
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
    if ($item1 == '' || $item2 == '') { return new \Exception("given ItemSimElemModel item1/2_code not exist."); }
    if ($this->getItemSimElem($item1, $item2)) {
      $this->tableGateway->update($data, array('item1_code' => $item1, 'item2_code' => $item2));
    } else {
      return new \Exception("ItemSimElemModel item1/2 dose not exist");
    }
  }

  public function saveItemSimElemByPoint($itemsInfo)
  {
    $item1_code = $itemsInfo['item1_code'];
    $item2_code = $itemsInfo['item2_code'];
    $i1p = +$itemsInfo['i1p'];
    $i2p = +$itemsInfo['i2p'];
    $i1p2 = $i1p * $i1p;
    $i2p2 = $i2p * $i2p;
    $i1xi2 = $i1p * $i2p;

    if ($item1_code == '' || $item2_code == '') { return new \Exception("given ItemSimElemModel item1/2_code not exist."); }
    $existRecord = $this->getItemSimElem($item1_code, $item2_code);
    if ($existRecord) {
      $this->tableGateway->update(
        array(
          'i1Sum' => $existRecord->i1Sum + $i1p,
          'i2Sum' => $existRecord->i2Sum + $i2p,
          'i1P2Sum' => $existRecord->i1P2Sum + $i1p2,
          'i2P2Sum' => $existRecord->i2P2Sum + $i2p2,
          'i1xi2Sum' => $existRecord->i1xi2Sum + $i1xi2,
          'num' => $existRecord->num + 1,
        ),
        array('item1_code' => $item1_code, 'item2_code' => $item2_code)
      );
    } else {
      $this->tableGateway->insert(array(
        'item1_code' => $item1_code,
        'item2_code' => $item2_code,
        'i1Sum' => $i1p,
        'i2Sum' => $i2p,
        'i1P2Sum' => $i1p2,
        'i2P2Sum' => $i2p2,
        'i1xi2Sum' => $i1xi2,
        'num' => 1,
      ));
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
