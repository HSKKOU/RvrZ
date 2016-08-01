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
    $recomCrtr = new RC01($this, $user_id);
    $recoms = $recomCrtr->createRecommendations();
    return $this->makeSuccessJson($recoms);
  }
  /* end Restful API methods */








  /* Utilities */
  /* end Utilities */


}


/* Recommendation */
class RC01 extends RecommendCreator
{
  protected $maxStar = 5.0;
  protected $minStar = 1.0;
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
    $inputs = $this->rvrCtrl->getInputsTable()->getInputsByUser($this->user_id);
    $gis = $this->createGazeInfoForEachItems($inputs);
    $reps = $this->calcReputationFromGazeInfoAll($gis);

    return array(
      'gis' => $gis,
      'reps' => $reps,
    );
  }

  protected function createGazeInfoForEachItems($inputs)
  {
    $gazeInfos = array();
    // for ($i=0; $i<count($inputs); $i++) {
    foreach ($inputs as $i => $val) {
      if (!isset($inputs[$i])) { continue; }
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

    // for ($i=0; $i<count($gazeInfos); $i++) {
    foreach ($gazeInfos as $i => $val) {
      if (!isset($gazeInfos[$i])) { continue; }
      $gazeInfos[$i]['aveDist'] /= $gazeInfos[$i]['count'];
    }

    ksort($gazeInfos);

    return $gazeInfos;
  }

  /* only total time */
  protected function calcReputationFromGazeInfoTotalTime($_gis) { return calcReputationFromGazeInfoBase($_gis, 'totalTime'); }
  /* only distance */
  protected function calcReputationFromGazeInfoAveDist($_gis) { return calcReputationFromGazeInfoBase($_gis, 'aveDist'); }

  /* calc reputation method base */
  protected function calcReputationFromGazeInfoBase($_gis, $_column_name)
  {
    $reps = array();

    $clMax = 0;
    $clMin = 100000000000;
    // for ($i=0; $i<count($_gis); $i++) {
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $gi = $_gis[$i];
      $giTT = $gi[$_column_name];
      if ($giTT > $clMax) { $clMax = $giTT; }
      if ($giTT < $clMin) { $clMin = $giTT; }
    }

    // for ($i=0; $i<count($_gis); $i++) {
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $gi = $_gis[$i];
      $giTT = $gi[$_column_name];
      $nCl = ($giTT - $clMin) / ($clMax - $clMin);
      $reps[$i] = ($this->maxStar - $this->minStar) * $nCl + $this->minStar;
    }

    return $reps;
  }

  /* totalTime and distance */
  protected function calcReputationFromGazeInfoAll($_gis)
  {
    $reps = array();

    $timeMax = 0;
    $timeMin = 100000000000;
    $distMax = 0;
    $distMin = 100000000000;
    // for ($i=0; $i<count($_gis); $i++) {
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $gi = $_gis[$i];
      $giTT = $gi['totalTime'];
      $giAD = $gi['aveDist'];
      if ($giTT > $timeMax) { $timeMax = $giTT; }
      if ($giTT < $timeMin) { $timeMin = $giTT; }
      if ($giAD > $distMax) { $distMax = $giAD; }
      if ($giAD < $distMin) { $distMin = $giAD; }
    }

    // for ($i=0; $i<count($_gis); $i++) {
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $gi = $_gis[$i];
      $giTT = $gi['totalTime'];
      $giAD = $gi['aveDist'];
      $nCl = sqrt(($giTT - $timeMin) / ($timeMax - $timeMin) * (1 - ($giAD - $distMin) / ($distMax - $distMin)) );
      $reps[$i] = ($this->maxStar - $this->minStar) * $nCl + $this->minStar;
    }

    return $reps;
  }
  /* end Recommendation */
}
