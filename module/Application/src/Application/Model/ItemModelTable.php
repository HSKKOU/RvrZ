<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql;

class ItemModelTable
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

  public function getItem($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find row $id");
    }

    return $row;
  }

  public function getItemByItemCode($code)
  {
    $rowSet = $this->tableGateway->select(array('item_code' => $code));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find row $code");
    }

    return $row;
  }

  public function getItemsByRandom($num)
  {
    $select = $this->tableGateway->getSql()->select();
    $select->order(array( new \Zend\Db\Sql\Expression("RAND()") ));
    $select->limit($num);

    // $sqlStr = $select->getSqlString();
    // var_dump($sqlStr);die;
    $rowSet = $this->tableGateway->selectWith($select);

    $ret = array();
    while ($row = $rowSet->current()) {
      $ret[] = $row->exchangeToArray();
      $rowSet->next();
    }

    return $ret;
  }

  public function saveItem(ItemModel $itemModel)
  {
    $data = array(
      'name' => $itemModel->name,
      'item_code' => $itemModel->item_code,
      'price' => $itemModel->price,
      'description' => $itemModel->description,
      'url_item' => $itemModel->url_item,
      'url_image' => $itemModel->url_image,
      'review_num' => $itemModel->review_num,
      'review_avg' => $itemModel->review_avg,
      'genre_id' => $itemModel->genre_id,
    );

    $id = (int)$itemModel->id;
    if ($id == 0) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getItem($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("ItemModel id dose not exist");
      }
    }
  }

  public function deleteItem($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }

  public function getItemSelect($col)
  {
    return $this->tableGateway->getSql()->select()->columns(array($col));
  }
}
