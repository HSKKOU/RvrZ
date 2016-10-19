<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ReputationModel;

class ReputationRestfulController extends AbstractRvrController
{
  protected $reputationTable;

  public function getReputationTable()
  {
    if(!$this->reputationTable)
    {
      $sm = $this->getServiceLocator();
      $this->reputationTable = $sm->get('Application\Model\ReputationModelTable');
    }

    return $this->reputationTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
    $gotModel = $this->getReputationTable()->getReputation($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'user_id' => $gotModel->user_id,
      'item_id' => $gotModel->item_id,
      'reputation' => $gotModel->reputation,
    ));
  }

  public function create($data)
  {
    $reps = $data['reps'];
    foreach ($reps as $rep) {
      $rep['user_id'] = $data['user_id'];
      $newModel = new ReputationModel();
      $newModel->exchangeArray($rep);
      $result = $this->getReputationTable()->saveReputation($newModel);
      $savedData = array();
      if ($result == 1) {
        $fetchList = $this->getListRaw();
        $savedData = $fetchList[count($fetchList)-1];
      }
    }

    return $this->makeSuccessJson($savedData);
  }







  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getReputationTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'user_id' => $row->user_id,
        'item_id' => $row->item_id,
        'reputation' => $row->reputation,
      );
    }

    return $data;
  }
}
