<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class InputsModelTable
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

  public function getInputs($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function saveInputs(InputsModel $inputsModel)
  {
    $data = array(
      'user_id' => $inputsModel->user_id,
      'user_positionV3' => $inputsModel->user_positionV3,
      'gaze_item_id' => $inputsModel->gaze_item_id,
      'gaze_item_positionV3' => $inputsModel->gaze_item_positionV3,
      'gaze_pointV2' => $inputsModel->gaze_pointV2,
      'around_item_ids' => $inputsModel->around_item_ids,
      'distance' => $inputsModel->distance,
      'time' => $inputsModel->time,
    );

    $id = (int)$inputsModel->id;
    if ($id == 0) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getInputs($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("InputsModel id dose not exist");
      }
    }
  }

  public function deleteInputs($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }
}
