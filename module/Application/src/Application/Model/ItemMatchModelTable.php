<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ItemMatchModelTable
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

  public function getItemMatch($item_id, $matched_item_id)
  {
    $item_id = (int)$item_id;
    $matched_item_id = (int)$matched_item_id;
    $rowSet = $this->tableGateway->select(array('item_id' => $item_id, 'matched_item_id' => $matched_item_id));
    $row = $rowSet->current();

    return $row;
  }

  public function saveItemMatch(ItemMatchModel $itemMatchModel)
  {
    $data = array(
      'item_id' => $itemMatchModel->item_id,
      'matched_item_id' => $itemMatchModel->matched_item_id,
      'similarity' => $itemMatchModel->similarity,
    );

    $item_id = (int)$itemMatchModel->item_id;
    $matched_item_id = (int)$itemMatchModel->matched_item_id;

    if ($this->getItemMatch($item_id, $matched_item_id)) {
      return $this->tableGateway->update($data, array('item_id' => $item_id, 'matched_item_id' => $matched_item_id));
    } else {
      return $this->tableGateway->insert($data);
    }
  }

  public function deleteItemMatch($id)
  {
    return $this->tableGateway->delete(array('item_id' => (int)$item_id, 'matched_item_id' => (int)$matched_item_id));
  }
}
