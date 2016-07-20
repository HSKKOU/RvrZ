<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\InputsModel;

class InputsRestfulController extends AbstractRestfulController
{
  protected $inputsTable;

  public function getInputsTable()
  {
    if(!$this->inputsTable)
    {
      $sm = $this->getServiceLocator();
      $this->inputsTable = $sm->get('Application\Model\InputsModelTable');
    }

    return $this->inputsTable;
  }

  public function getList()
  {
    return new JsonModel($this->getListRaw());
  }

  public function get($id)
  {
    $gotModel = $this->getInputsTable()->getInputs($id);
    return new JsonModel(array(
      'id' => $gotModel->id,
      'user_id' => $gotModel->user_id,
      'user_positionV3' => $gotModel->user_positionV3,
      'gaze_item_id' => $gotModel->gaze_item_id,
      'gaze_item_positionV3' => $gotModel->gaze_item_positionV3,
      'gaze_pointV2' => $gotModel->gaze_pointV2,
      'around_item_ids' => $gotModel->around_item_ids,
      'gaze_at_time' => $gotModel->gaze_at_time,
      'distance' => $gotModel->distance,
      'time' => $gotModel->time,
    ));
  }

  public function create($data)
  {
    if (!array_key_exists('inputs', $data)) { return new JsonModel(array( 'result' => 0, 'data' => 'no inputs', 'opt' => $data )); }

    $newModelArray = array();
    foreach ($data['inputs'] as $key => $val) {
      $newModel = new InputsModel();
      $newModel->exchangeArray($val);
      $result = $this->getInputsTable()->saveInputs($newModel);
      $savedData = array();
      if ($result == 1) {
        $fetchList = $this->getListRaw();
        $savedData = $fetchList[count($fetchList)-1];
        $newModelArray[] = $savedData;
      }
    }

    return new JsonModel(array(
      'result' => $result,
      'data' => $newModelArray,
    ));
  }

  public function update($id, $data)
  {
    $newModel = new InputsModel();
    $newModel->exchangeArray($data);
    $newModel->id = $id;
    $result = $this->getInputsTable()->saveInputs($newModel);
    $savedData = array();
    if ($result == 1) {
      $savedData = $this->get($id);
    }

    return new JsonModel(array(
      'result' => $result,
      'data' => $newModel,
    ));
  }

  public function delete($id)
  {
    $delModel = $this->getInputsTable()->getInputs($id);
    $result = $this->getInputsTable()->deleteInputs($id);

    return new JsonModel(array(
      'result' => $result,
      'data' => $delModel->exchangeToArray(),
    ));
  }






  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getInputsTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'user_id' => $row->user_id,
        'user_positionV3' => $row->user_positionV3,
        'gaze_item_id' => $row->gaze_item_id,
        'gaze_item_positionV3' => $row->gaze_item_positionV3,
        'gaze_pointV2' => $row->gaze_pointV2,
        'around_item_ids' => $row->around_item_ids,
        'gaze_at_time' => $row->gaze_at_time,
        'distance' => $row->distance,
        'time' => $row->time,
      );
    }

    return $data;
  }
}
