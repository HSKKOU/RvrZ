<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class DisplayItemModelTable
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

  public function getDisplayItem($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function getLastDisplayItem()
  {
    $id = $this->getLastId();
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function saveDisplayItem(DisplayItemModel $displayItemModel)
  {
    $data = array(
      'user_id' => $displayItemModel->user_id,
      'item_id' => $displayItemModel->item_id,
    );

    $id = (int)$displayItemModel->id;
    if ($id == 0) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getUser($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("UserModel id dose not exist");
      }
    }
  }

  public function deleteDisplayItem($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }


  public function getLastId()
  {
    return $this->tableGateway->lastInsertValue;
  }
}
