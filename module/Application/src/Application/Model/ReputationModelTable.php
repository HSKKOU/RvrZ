<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ReputationModelTable
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

  public function getReputation($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function getLastReputation()
  {
    $id = $this->getLastId();
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function saveReputation(ReputationModel $reputationModel)
  {
    $data = array(
      'user_id' => $reputationModel->user_id,
      'item_id' => $reputationModel->item_id,
      'reputation' => $reputationModel->reputation,
    );

    $id = (int)$reputationModel->id;
    if ($id == 0) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getReputation($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("ReputationModel id dose not exist");
      }
    }
  }

  public function deleteReputation($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }


  public function getLastId()
  {
    return $this->tableGateway->lastInsertValue;
  }
}
