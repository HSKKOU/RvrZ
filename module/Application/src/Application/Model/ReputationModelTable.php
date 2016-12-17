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

  public function getRepByUseridAndType($user_id, $type)
  {
    $user_id = (int)$user_id;
    $rowSet = $this->tableGateway->select(array('user_id' => $user_id, 'type' => $type));
    $retArr = array();
    while ($row = $rowSet->current()) {
      $retArr[] = $row->exchangeToArray();
      $rowSet->next();
    }
    return $retArr;
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
      'rank' => $reputationModel->rank,
      'expected' => $reputationModel->expected,
      'type' => $reputationModel->type,
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
