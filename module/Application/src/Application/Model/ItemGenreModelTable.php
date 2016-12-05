<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ItemGenreModelTable
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

  public function getItemGenre($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function getItemGenreRelated($pids)
  {
    $select = $this->tableGateway->getSql()->select();
    $select->where->in('second_top', $pids);
    $rowSet = $this->tableGateway->selectWith($select);

    while ($row = $rowSet->current()) {
      $pids[] = +$row->id;
      $rowSet->next();
    }
    return $pids;
  }

  public function getLastItemGenre()
  {
    $id = $this->getLastId();
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function saveItemGenre(ItemGenreModel $itemGenreModel)
  {
    $data = array(
      'genre_name' => $itemGenreModel->genre_name,
      'parent_genre_id' => $itemGenreModel->parent_genre_id,
      'id_tree' => $itemGenreModel->id_tree,
      'second_top' => $itemGenreModel->second_top,
    );

    $id = (int)$itemGenreModel->id;
    if ($id == -1) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getItemGenre($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("ItemGenreModel id dose not exist");
      }
    }
  }

  public function deleteItemGenre($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }


  public function getLastId()
  {
    return $this->tableGateway->lastInsertValue;
  }
}
