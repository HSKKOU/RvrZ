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
    $recomCrtr = new RecommendCreator($this, $user_id);
    $recoms = $recomCrtr->createRecommendations();
    return $this->makeSuccessJson($recoms);
  }
  /* end Restful API methods */




  /* Recommendation */

  /* end Recommendation */



  /* Utilities */
  /* end Utilities */


}

class RecommendCreator
{
  private $user_id;
  private $rvrCtrl;

  protected $distThreshold = 200.0;
  protected $maxStar = 5.0;
  protected $minStar = 3.0;

  public function __construct($_rvrCtrl, $_user_id)
  {
    $this->rvrCtrl = $_rvrCtrl;
    $this->user_id = $_user_id;
  }

  public function createRecommendations()
  {
    $data = array();

    $inputs = $this->rvrCtrl->getInputsTable()->getInputsByUser($this->user_id);
    $gis = $this->createGazeInfoForEachItems($inputs);

    $data = $gis;

    return $data;
  }

  private function createGazeInfoForEachItems($inputs)
  {
    $gazeInfos = array();
    for ($i=0; $i<count($inputs); $i++) {
      $ip = $inputs[$i];
      if ($ip->gaze_item_id == 0) { continue; }
      if (!isset($gazeInfos[$ip->gaze_item_id])) {
        $gazeInfos[$ip->gaze_item_id] = array(
          'count' => 1,
          'aveDist' => floatval($ip->distance),
          'totalTime' => floatval($ip->time),
        );
      } else {
        $gi = $gazeInfos[$ip->gaze_item_id];
        $gazeInfos[$ip->gaze_item_id] = array(
          'count' => $gi['count']+1,
          'aveDist' => $gi['aveDist'] + floatval($ip->distance),
          'totalTime' => $gi['totalTime'] + floatval($ip->time),
        );
      }
    }

    for ($i=0; $i<count($gazeInfos); $i++) { $gazeInfos[$i]['aveDist'] /= $gazeInfos[$i]['count']; }

    ksort($gazeInfos);

    return $gazeInfos;
  }

  private function calcReputationFromGazeInfo()
  {

  }
}
