<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;
use Application\Model\ItemMatchModel;
use Application\Model\InputsModel;

class RvrRestfulController extends AbstractRvrController
{
  protected $itemTable;
  protected $reviewTable;
  protected $inputsTable;
  protected $itemMatchTable;

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
  public function getItemMatchTable() {
    if(!$this->itemMatchTable) { $this->itemMatchTable = $this->getServiceLocator()->get('Application\Model\ItemMatchModelTable'); }
    return $this->itemMatchTable;
  }
  /* end get db tables reference */




  /* Restful API methods */
  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($user_id)
  {
    if ($user_id == 'updateItemMatchDataSet') {
      $this->updateItemMatchDataSet();
      return $this->makeSuccessJson('updated DataSet');
    }

    // for DEBUG
    if (true) {
      $items = array();
      for ($i=0; $i<50; $i++) {
        $items[] = array(
          "id" => $i,
          "title" => "Item " . $i,
          "img" => "/img/index.png",
        );
      }
      return $this->makeSuccessJson(array("items" => $items));
    }

    // $recomCrtr = new RC01OnlyTime($this, $user_id);
    // $recomCrtr = new RC01OnlyDist($this, $user_id);
    $recomCrtr = new RC01All($this, $user_id);
    $recoms = $recomCrtr->createRecommendations();

    return $this->makeSuccessJson($recoms);
  }
  /* end Restful API methods */








  /* Utilities */
  private function updateItemMatchDataSet()
  {
    $it = $this->getItemTable();
    $imt = $this->getItemMatchTable();
    $rt = $this->getReviewTable();

    // TODO: should modify for memory leak
    $scoreByItemUser = array();
    $rowSet = $rt->fetchAll();
    while ($row = $rowSet->current()) {
      $itemId = $row->item_id;
      $userName = $row->user_name;
      if (!array_key_exists($itemId, $scoreByItemUser)) { $scoreByItemUser[$itemId] = array(); }
      $scoreByItemUser[$itemId][$userName] = $row->point;
      $rowSet->next();
    }

    // TODO: should modify for memory leak
    foreach ($scoreByItemUser as $iid1 => $item1) {
      foreach ($scoreByItemUser as $iid2 => $item2) {
        if ($iid1 >= $iid2) { continue; }
        // $s = $this->calcItemSimilarityEuclid($item1, $item2);
        $s = $this->calcItemSimilarityPearson($item1, $item2);
        $ima = array(
          'item_id' => $iid1,
          'matched_item_id' => $iid2,
          'similarity' => $s,
        );
        $im = new ItemMatchModel();
        $im->exchangeArray($ima);
        $imt->saveItemMatch($im);
      }
    }
  }

  private function calcItemSimilarityEuclid($item1, $item2)
  {
    $simSquareSum = 0.0;
    foreach ($item1 as $un1 => $p1) {
      if (!array_key_exists($un1, $item2)) { continue; }
      $simSquareSum += floatval(pow($p1-$item2[$un1], 2));
    }

    return 1.0 / (1.0 + sqrt($simSquareSum));
  }

  private function calcItemSimilarityPearson($item1, $item2)
  {
    $i1Sum = 0.0;
    $i2Sum = 0.0;
    $i1Sp2 = 0.0;
    $i2Sp2 = 0.0;
    $i1xi2Sum = 0.0;
    $cnt = 0;
    $denEps = 0.00000001;
    foreach ($item1 as $un1 => $p1) {
      if (!array_key_exists($un1, $item2)) { continue; }
      $p2 = $item2[$un1];
      $i1Sum += floatval($p1);
      $i2Sum += floatval($p2);
      $i1Sp2 += floatval(pow($p1, 2));
      $i2Sp2 += floatval(pow($p2, 2));
      $i1xi2Sum += floatval($p1 * $p2);
      $cnt++;
    }

    if ($cnt == 0) { return 0.0; }

    $num = $i1xi2Sum - $i1Sum * $i2Sum / floatval($cnt);
    $den = sqrt(($i1Sp2 - pow($i1Sum, 2) / floatval($cnt)) * ($i2Sp2 - pow($i2Sum, 2) / floatval($cnt)));

    if ($den < $denEps) { return 0.0; }

    return ($num / $den + 1) / 2;
  }
  /* end Utilities */


}






/* Recommendation */
class RC01 extends RecommendCreator
{
  protected $maxStar = 5.0;
  protected $minStar = 1.0;

  protected function calcReputationFromGazeInfo($_gis){ return array(); }
}

class RC01OnlyTime extends RC01
{
  protected function calcReputationFromGazeInfo($_gis)
  {
    $reps = array();

    $mm = $this->selectMaxMinValueWithInGazeInfo($_gis, 'totalTime');
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $nCl = $this->normalizeData($_gis[$i]['totalTime'], $mm['max'], $mm['min']);
      $reps[$i] = $this->applyStarValue($nCl);
    }

    return $reps;
  }
}

class RC01OnlyDist extends RC01
{
  protected function calcReputationFromGazeInfo($_gis)
  {
    $reps = array();

    $mm = $this->selectMaxMinValueWithInGazeInfo($_gis, 'aveDist');
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $nCl = $this->normalizeDataInv($_gis[$i]['aveDist'], $mm['max'], $mm['min']);
      $reps[$i] = $this->applyStarValue($nCl);
    }

    return $reps;
  }
}

class RC01All extends RC01
{
  protected function calcReputationFromGazeInfo($_gis)
  {
    $reps = array();

    $mmTT = $this->selectMaxMinValueWithInGazeInfo($_gis, 'totalTime');
    $mmAD = $this->selectMaxMinValueWithInGazeInfo($_gis, 'aveDist');
    foreach ($_gis as $i => $val) {
      if (!isset($_gis[$i])) { continue; }
      $nClTT = $this->normalizeData($_gis[$i]['totalTime'], $mmTT['max'], $mmTT['min']);
      $nClAD = $this->normalizeDataInv($_gis[$i]['aveDist'], $mmAD['max'], $mmAD['min']);
      $nCl = sqrt($nClTT * $nClAD);
      $reps[$i] = $this->applyStarValue($nCl);
    }

    return $reps;
  }
}





abstract class RecommendCreator
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
    $reps = $this->calcReputationFromGazeInfo($gis);
    $repsSim = $this->calcReptationSimilarity($reps);

    // return array(
    //   'gis' => $gis,
    //   'reps' => $reps,
    //   'sim' => $repsSim,
    // );

    return $repsSim;
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

  protected function selectMaxMinValueWithInGazeInfo($_gis, $_column_name)
  {
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

    return array("max" => $clMax, "min" => $clMin);
  }

  protected function applyStarValue($_normalized_val) { return ($this->maxStar - $this->minStar) * $_normalized_val + $this->minStar; }

  protected abstract function calcReputationFromGazeInfo($_gis);


  //
  protected function calcReptationSimilarity($_reps, $maxCnt = 3)
  {
    $imt = $this->rvrCtrl->getItemMatchTable();
    $rt = $this->rvrCtrl->getReviewTable();
    $scores = array();
    $totalSim = array();
    $rankings = array();

    foreach ($_reps as $rKey => $rep) {
      $sims = $imt->getItemMatches(intval($rKey));
      foreach ($sims as $k => $v) {
        $targetId = intval($v['matched_item_id']);
        if (array_key_exists($targetId, $_reps)) { continue; }
        if (!array_key_exists($targetId, $scores)) { $scores[$targetId] = 0.0; }
        $scores[$targetId] += floatval($v['similarity']) * floatval($rep);
        if (!array_key_exists($targetId, $totalSim)) { $totalSim[$targetId] = 0.0; }
        $totalSim[$targetId] += floatval($v['similarity']);
      }
    }

    foreach ($scores as $sk => $score) { $rankings[$sk] = $score / $totalSim[$sk]; }

    arsort($rankings);

    $result = array();
    $cnt = 0;
    foreach ($rankings as $rk => $rv) {
      $result[] = array(
        'itemInfo' => $this->getItemInfo($rk),
        'score' => $rv,
      );
      $cnt++;
      if ($cnt >= $maxCnt) { break; }
    }

    return $result;
  }

  private function getItemInfo($id)
  {
    $itemTable = $this->rvrCtrl->getItemTable();
    $item = $itemTable->getItem($id);
    return $item->exchangeToArray();
  }


  protected function normalizeData($_di, $_dMax, $_dMin) { return ($_di - $_dMin) / ($_dMax - $_dMin); }
  protected function normalizeDataInv($_di, $_dMax, $_dMin) { return 1.0 - ($_di - $_dMin) / ($_dMax - $_dMin); }
  /* end Recommendation */
}
