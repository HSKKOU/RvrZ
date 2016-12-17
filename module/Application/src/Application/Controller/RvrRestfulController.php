<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;
use Application\Model\ItemMatchModel;
use Application\Model\InputsModel;

use Application\Model\ReviewModel;
use Application\Model\ReviewModelTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;


class RvrRestfulController extends AbstractRvrController
{
  protected $itemTable;
  protected $reviewTable;
  protected $inputsTable;
  protected $itemMatchTable;
  protected $reviewUserTable;
  protected $itemSimElemTable;

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
  public function getReviewUserTable() {
    if(!$this->reviewUserTable) { $this->reviewUserTable = $this->getServiceLocator()->get('Application\Model\ReviewUserModelTable'); }
    return $this->reviewUserTable;
  }
  public function getItemSimElemTable() {
    if(!$this->itemSimElemTable) { $this->itemSimElemTable = $this->getServiceLocator()->get('Application\Model\ItemSimElemModelTable'); }
    return $this->itemSimElemTable;
  }
  /* end get db tables reference */




  /* Restful API methods */
  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($user_id)
  {
    // if (preg_match('/updateItemMatchDataSet/', $user_id)) {
    //   $itemIdSp = explode("_", $user_id);
    //   $num = $this->updateItemMatchDataSet_0001($itemIdSp[1], $itemIdSp[2]);
    if ($user_id == 'updateItemMatchDataSet') {
      $this->updateItemMatchDataSet();
      return $this->makeSuccessJson('updated DataSet ');
    } else if ($user_id == 'updateItemReviewDataSet') {
      $this->updateItemReviewDataSet();
      return $this->makeSuccessJson('updated Review in Item DataSet');
    } else if ($user_id == 'createItemSimTable') {
      $this->createItemSimTable();
      return $this->makeSuccessJson('created ItemSimElem Table');
    }

    // for DEBUG
    if (
      // true
      false
  ) {
      $items = array();
      for ($i=0; $i<50; $i++) {
        $items[] = array(
          "itemInfo" => array(
            "id" => $i,
            "name" => "Item " . $i,
            "url_image" => "/img/index.png",
          ),
          "score" => rand(0,1000)/1000.0*4.0 + 1.0,
        );
      }
      return $this->makeSuccessJson($items);
    }



    $requestSp = explode("_", $user_id);
    $type = $requestSp[0];
    $uId = +$requestSp[1];
    $recomCrtr;
    switch ($type) {
      case 'all':
        $recomCrtr = new RC01All($this, $uId);
        break;
      case 'eye':
        $recomCrtr = new RC01OnlyTime($this, $uId);
        break;
    }

    // $recomCrtr = new RC01OnlyTime($this, $user_id);
    // $recomCrtr = new RC01OnlyDist($this, $user_id);
    $recoms = $recomCrtr->createRecommendations();
    shuffle($recoms);

    return $this->makeSuccessJson($recoms);
  }
  /* end Restful API methods */








  /* Utilities */
  private function updateItemMatchDataSet()
  {
    $imTable = $this->getItemMatchTable();
    $iseTable = $this->getItemSimElemTable();

    $iseDatas = $iseTable->fetchAll();

    foreach ($iseDatas as $isk => $isv) {
      $sim = 0;
      if ($isv->num == 0) { break; }

      $num = $isv->i1xi2Sum - $isv->i1Sum * $isv->i2Sum / floatval($isv->num);
      $den = sqrt(($isv->i1P2Sum - pow($isv->i1Sum, 2) / floatval($isv->num)) * ($isv->i2P2Sum - pow($isv->i2Sum, 2) / floatval($isv->num)));

      if (abs($den) < 0.00000001) {
        $sim = 0.0;
      } else {
        $sim = $num / $den;
      }

      if ($sim == 0.0) { continue; }

      $im = new ItemMatchModel();
      $im->exchangeArray(array(
        'item_id' => $isv->item1_code,
        'matched_item_id' => $isv->item2_code,
        'similarity' => $sim,
        'users_num' => $isv->num,
      ));

      $imTable->saveItemMatch($im);
    }
  }

  private function updateItemMatchDataSet_0001($itemId1, $itemId2)
  {
    if (+$itemId1 == 1) { return -1; }
    if (+$itemId1 >= +$itemId2) { return -1; }

    $it = $this->getItemTable();
    $imt = $this->getItemMatchTable();

    $item1 = $it->getItem(+$itemId1);
    $item2 = $it->getItem(+$itemId2);

    $calcRes = $this->calcItemSimilarityPearson($item1, $item2);

    if (+$calcRes['num'] == 0 || +$calcRes['sim'] == 0) { return 0; }
    $ima = array(
      'item_id' => +$itemId1,
      'matched_item_id' => +$itemId2,
      'similarity' => +$calcRes['sim'],
      'users_num' => +$calcRes['num'],
    );
    $im = new ItemMatchModel();
    $im->exchangeArray($ima);
    $imt->saveItemMatch($im);

    return +$calcRes['num'];
  }

  private function createItemSimTable()
  {
    // create Review Model Table
    $sm = $this->getServiceLocator();

    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    $resultSetPrototype = new ResultSet();
    $resultSetPrototype->setArrayObjectPrototype(new ReviewModel());

    $rts = array();
    for ($i=0; $i<6; $i++) {
      $rn = substr('0'.($i+1), -2, 2);
      $reviewTable = new ReviewModelTable(new TableGateway('reviews_'.$rn, $dbAdapter, null, $resultSetPrototype));
      $rts[] = $reviewTable;
    }


    $iseTable = $this->getItemSimElemTable();

    // for update Debug
    // $iseTable->saveItemSimElemByPoint(array(
    //   'item1_code' => 'aaa:123',
    //   'item2_code' => 'aaa:123',
    //   'i1p' => 4,
    //   'i2p' => 3,
    // ));
    // return;

    $reviewUsersArray = $this->getReviewUserTable()->fetchAll();

    $itemSelect = $this->getItemTable()->getItemSelect('item_code');

    foreach ($reviewUsersArray as $rk => $reviewUsers) {
      $user_name = $reviewUsers->user_name;
      $reviewNum = $reviewUsers->reviews;

      unset($reviewUsers);

      // Now, $user_name and $reviewNum exist.

      // item_code => point
      $reviews = array();

      for ($i=0; $i<count($rts); $i++) {
        $rt = $rts[$i];
        $revs = $rt->getReviewsByUserNameWithItemSelect($user_name, $itemSelect);
        if (count($revs) == 0) { continue; }
        for ($j=0; $j<count($revs); $j++) {
          $rev = $revs[$j];
          $reviews[$rev->item_id] = +$rev->point;
        }
        // $reviews = array_merge($reviews, $revs);
      }

      if (count($reviews) < 2) { continue; }

      // save Item Similarity Elements
      $itemCodes = array_keys($reviews);
      usort($itemCodes, 'strcmp');
      for ($ri=0; $ri<count($itemCodes); $ri++) {
        $item1_code = $itemCodes[$ri];
        for ($rj=$ri+1; $rj<count($itemCodes); $rj++) {
          $item2_code = $itemCodes[$rj];

          $iseData = array(
            'item1_code' => $item1_code,
            'item2_code' => $item2_code,
            'i1p' => $reviews[$item1_code],
            'i2p' => $reviews[$item2_code],
          );
          $iseTable->saveItemSimElemByPoint($iseData);
        }
      }

      unset($reviews);
      unset($itemCodes);
    }
  }

  private function calcItemSimilarityPearson($item1, $item2)
  {
    $sm = $this->getServiceLocator();

    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    $resultSetPrototype = new ResultSet();
    $resultSetPrototype->setArrayObjectPrototype(new ReviewModel());

    $rts = array();
    for ($i=0; $i<6; $i++) {
      $rn = substr('0'.($i+1), -2, 2);
      $reviewTable = new ReviewModelTable(new TableGateway('reviews_'.$rn, $dbAdapter, null, $resultSetPrototype));
      $rts[] = $reviewTable;
    }

    $itemId1 = $item1->item_code;
    $itemId2 = $item2->item_code;

    $item1Revs = array();
    $item1RevsCnt = 0;
    for ($i=0; $i<6; $i++) {
      $revs = $rts[$i]->getReviewsByItemId($itemId1);
      for ($ri=0; $ri<count($revs); $ri++) {
        $rev = $revs[$ri];
        $item1Revs[$rev->user_name] = $rev->point;
        $item1RevsCnt++;
      }
      unset($revs);
      unset($rev);
      if ($item1RevsCnt >= $item1->review_num) { break; }
    }
    $item2Revs = array();
    $item2RevsCnt = 0;
    for ($i=0; $i<6; $i++) {
      $revs = $rts[$i]->getReviewsByItemId($itemId2);
      for ($ri=0; $ri<count($revs); $ri++) {
        $rev = $revs[$ri];
        $item2Revs[$rev->user_name] = $rev->point;
        $item2RevsCnt++;
      }
      unset($revs);
      unset($rev);
      if ($item2RevsCnt >= $item2->review_num) { break; }
    }

    // unset($sm);
    // unset($dbAdapter);
    // unset($resultSetPrototype);
    // unset($rts);

    $i1Sum = 0.0;
    $i2Sum = 0.0;
    $i1Sp2 = 0.0;
    $i2Sp2 = 0.0;
    $i1xi2Sum = 0.0;
    $cnt = 0;
    $denEps = 0.00000001;

    foreach ($item1Revs as $un => $p1) {
      if (!array_key_exists($un, $item2Revs)) { continue; }
      $p2 = $item2Revs[$un];
      $i1Sum += floatval($p1);
      $i2Sum += floatval($p2);
      $i1Sp2 += floatval(pow($p1, 2));
      $i2Sp2 += floatval(pow($p2, 2));
      $i1xi2Sum += floatval($p1 * $p2);
      $cnt++;
    }

    unset($item1Revs);
    unset($item2Revs);

    if ($cnt == 0) {
      return array(
        'sim' => 0.0,
        'num' => 0,
      );
    } else if ($cnt == 1 && $i1Sum == $i2Sum) {
      return array(
        'sim' => 1.0,
        'num' => 1,
      );
    }

    $num = $i1xi2Sum - $i1Sum * $i2Sum / floatval($cnt);
    $den = sqrt(($i1Sp2 - pow($i1Sum, 2) / floatval($cnt)) * ($i2Sp2 - pow($i2Sum, 2) / floatval($cnt)));

    if ($den < $denEps) {
      return array(
        'sim' => 0.0,
        'num' => $cnt,
      );
    }

    return array(
      'sim' => ($num / $den + 1) / 2,
      'num' => $cnt,
    );
  }
  /* end Utilities */


  private function updateItemReviewDataSet()
  {
    $sm = $this->getServiceLocator();

    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    $resultSetPrototype = new ResultSet();
    $resultSetPrototype->setArrayObjectPrototype(new ReviewModel());

    $rts = array();
    for ($i=0; $i<6; $i++) {
      $rn = substr('0'.($i+1), -2, 2);
      $reviewTable = new ReviewModelTable(new TableGateway('reviews_'.$rn, $dbAdapter, null, $resultSetPrototype));
      $rts[] = $reviewTable;
    }

    $it = $this->getItemTable();
    $itemSet = $it->fetchAll();
    while ($item = $itemSet->current()) {
      $itemCode = $item->item_code;

      $review_num = 0;
      $review_avg = 0.0;

      for ($i=0; $i<count($rts); $i++) {
        $rt = $rts[$i];
        $rs = $rt->getReviewsByItemId($itemCode);
        if (count($rs) == 0) { continue; }

        foreach ($rs as $rsk => $rsv) { $review_avg += floatval($rsv->point); }
        $review_num += count($rs);
      }

      if ($review_num > 0) { $review_avg /= $review_num; }

      $item->review_num = $review_num;
      $item->review_avg = $review_avg;

      // var_dump($item);die;

      $it->saveItem($item);

      $itemSet->next();
    }
  }


}











/* Recommendation */
class RC01 extends RecommendCreator
{
  protected $maxStar = 5.0;
  protected $minStar = 3.0;

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
    $repsSim = $this->calcReptationSimilarity($reps, 50);

    // return array(
    //   'gis' => $gis,
    //   'reps' => $reps,
    //   'sim' => $repsSim,
    // );
    return $repsSim;
  }

  protected function createGazeInfoForEachItems($inputs)
  {
    $it = $this->rvrCtrl->getItemTable();

    $gazeInfos = array();
    // for ($i=0; $i<count($inputs); $i++) {
    foreach ($inputs as $i => $val) {
      if (!isset($inputs[$i])) { continue; }
      $ip = $inputs[$i];
      if ($ip->gaze_item_id == 0) { continue; }
      $itemInfo = $it->getItem($ip->gaze_item_id);
      if (!isset($gazeInfos[$itemInfo->item_code])) {
        $gazeInfos[$itemInfo->item_code] = array(
          'count' => 1,
          'aveDist' => floatval($ip->distance),
          'totalTime' => floatval($ip->time),
        );
      } else {
        $gi = $gazeInfos[$itemInfo->item_code];
        $gazeInfos[$itemInfo->item_code] = array(
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
  protected function calcReptationSimilarity($_reps, $maxCnt = 50)
  {
    $imt = $this->rvrCtrl->getItemMatchTable();
    $rt = $this->rvrCtrl->getReviewTable();
    $scores = array();
    $totalSim = array();
    $rankings = array();

    foreach ($_reps as $rKey => $rep) {
      $sims = $imt->getItemMatches($rKey);
      foreach ($sims as $k => $v) {
        $targetId = $v['matched_item_id'];
        if (array_key_exists($targetId, $_reps)) { continue; }
        if (!array_key_exists($targetId, $scores)) { $scores[$targetId] = 0.0; }
        $scores[$targetId] += floatval($v['similarity']) * floatval($rep);
        if (!array_key_exists($targetId, $totalSim)) { $totalSim[$targetId] = 0.0; }
        $totalSim[$targetId] += floatval($v['similarity']);
      }
    }

    foreach ($scores as $sk => $score) {
      if ($totalSim[$sk] == 0) { $rankings[$sk] = 0.0; continue; }
      $rankings[$sk] = $score / $totalSim[$sk];
    }

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

  private function getItemInfo($code)
  {
    $itemTable = $this->rvrCtrl->getItemTable();
    $item = $itemTable->getItemByItemCode($code);
    return $item->exchangeToArray();
  }


  protected function normalizeData($_di, $_dMax, $_dMin) { return ($_di - $_dMin) / ($_dMax - $_dMin); }
  protected function normalizeDataInv($_di, $_dMax, $_dMin) { return 1.0 - ($_di - $_dMin) / ($_dMax - $_dMin); }
  /* end Recommendation */
}
