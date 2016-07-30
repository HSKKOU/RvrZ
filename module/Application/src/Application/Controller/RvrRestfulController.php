<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;
use Application\Model\InputsModel;

class RvrRestfulController extends AbstractRvrController
{
  protected $itemTable;
  protected $reviewTable;
  protected $inputsTable;

  public function indexAction() { return new ViewModel(); }



  /* get db tables reference */
  public function getItemTable() {
    if(!$this->itemTable) { $this->itemTable = $this->getServiceLocator()->get('Application\Model\ItemModelTable'); }
    return $this->itemTable;
  }
  public function getReviewTable() {
    if(!$this->reviewTable) { $this->reviewTable = $this->getServiceLocator()->get('Application\Model\ReviewModelTable'); }
    return $this->reviewTable;
  }
  public function getInputsTable() {
    if(!$this->inputsTable) { $this->inputsTable = $this->getServiceLocator()->get('Application\Model\InputsModelTable'); }
    return $this->inputsTable;
  }
  /* end get db tables reference */




  /* Restful API methods */
  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($user_id)
  {
    $recoms = $this->createRecommendations($user_id);
    return $this->makeSuccessJson($recoms);
  }
  /* end Restful API methods */




  /* Recommendation */
  public function createRecommendations($user_id)
  {
    $data = array();

    $inputs = $this->getInputsTable()->getInputsByUser($user_id);
    $gis = $this->createGazeInfoForEachItems($inputs);

    $data = $gis;

    return $data;
  }

  public function createGazeInfoForEachItems($inputs)
  {
    $gazeInfoForEachItems = array();
    for ($i=0; $i<count($inputs); $i++) {
      $ip = $inputs[$i];
      if($ip->gaze_item_id == 0) { continue; }
      if (!isset($gazeInfoForEachItems[$ip->gaze_item_id])) {
        $gazeInfoForEachItems[$ip->gaze_item_id] = array(
          'count' => 1,
          'aveDist' => floatval($ip->distance),
          'totalTime' => floatval($ip->time),
        );
      } else {
        $gi = $gazeInfoForEachItems[$ip->gaze_item_id];
        $gazeInfoForEachItems[$ip->gaze_item_id] = array(
          'count' => $gi['count']+1,
          'aveDist' => ($gi['aveDist'] * $gi['count'] + floatval($ip->distance)) / ($gi['count']+1),
          'totalTime' => $gi['totalTime'] + floatval($ip->time),
        );
      }
    }

    ksort($gazeInfoForEachItems);

    return $gazeInfoForEachItems;
  }
  /* end Recommendation */



  /* Utilities */
  /* end Utilities */


}
