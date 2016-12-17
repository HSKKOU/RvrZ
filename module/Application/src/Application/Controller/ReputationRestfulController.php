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
    if (preg_match('/analysis/', $id)) {
      $sp = explode("_", $id);
      $userId = +$sp[1];
      $type = $sp[2];
      return $this->makeSuccessJson(
        array(
          'dcg' => $this->calcDCG($userId, $type),
          'pearson' => $this->calcPearson($userId, $type),
          'mae' => $this->calcMAE($userId, $type),
          'precision' => $this->calcPrecision($userId, $type),
        )
      );
    }

    $gotModel = $this->getReputationTable()->getReputation($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'user_id' => $gotModel->user_id,
      'item_id' => $gotModel->item_id,
      'reputation' => $gotModel->reputation,
      'rank' => $gotModel->rank,
      'expected' => $gotModel->expected,
      'type' => $gotModel->type,
    ));
  }

  public function create($data)
  {
    $reps = $data['reps'];
    foreach ($reps as $rep) {
      $rep['user_id'] = $data['user_id'];
      $rep['type'] = $data['type'];
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




  public function calcDCG($user_id, $type)
  {
    $repTable = $this->getReputationTable();
    $reps = $repTable->getRepByUseridAndType($user_id, $type);

    array_multisort(array_column($reps, 'expected'), SORT_DESC, $reps);

    $result = 0;
    $ideal = 0;

    for ($i=0; $i<count($reps); $i++) {
      $rep = $reps[$i];

      $ideal += ( (count($reps) - $i) / ($i==0? 1:log($i+1, 2)) );

      $rank = $i+1;

      if($rank != intval($rep['rank'])) { continue; }

      $rel = count($reps) - $rank;
      if ($i > 0) {
        $rel /= log($rank, 2);
      }
      $result += $rel;
    }

    $nDCG = 0;
    if ($ideal != 0) { $nDCG = $result/$ideal; }

    return array('DCG' => $result, 'IDCG' => $ideal, 'nDCG' => $nDCG);
  }

  public function calcPearson($user_id, $type)
  {
    $repTable = $this->getReputationTable();
    $reps = $repTable->getRepByUseridAndType($user_id, $type);

    array_multisort(array_column($reps, 'expected'), SORT_DESC, $reps);

    $result = 0;

    $i1Sum = 0.0;
    $i2Sum = 0.0;
    $i1Sp2 = 0.0;
    $i2Sp2 = 0.0;
    $i1xi2Sum = 0.0;
    $cnt = 0;
    $denEps = 0.00000001;

    for ($i=0; $i<count($reps); $i++) {
      $idealRank = $i+1;
      $realRank = intval($reps[$i]['rank']);

      $i1Sum += floatval($idealRank);
      $i2Sum += floatval($realRank);
      $i1Sp2 += floatval(pow($idealRank, 2));
      $i2Sp2 += floatval(pow($realRank, 2));
      $i1xi2Sum += floatval($idealRank * $realRank);
      $cnt++;
    }

    if ($cnt == 0) { return 0; }

    $num = $i1xi2Sum - $i1Sum * $i2Sum / floatval($cnt);
    $den = sqrt(($i1Sp2 - pow($i1Sum, 2) / floatval($cnt)) * ($i2Sp2 - pow($i2Sum, 2) / floatval($cnt)));

    if ($den == 0) { return 0; }

    return $num / $den;
  }

  public function calcMAE($user_id, $type)
  {
    $repTable = $this->getReputationTable();
    $reps = $repTable->getRepByUseridAndType($user_id, $type);

    $result = 0;

    if (count($reps) == 0) { return null; }

    for ($i=0; $i<count($reps); $i++) {
      $idealRep = floatval($reps[$i]['expected']);
      $realRep = floatval($reps[$i]['reputation']);

      $result += abs($idealRep - $realRep);
    }

    return $result / count($reps);
  }

  public function calcPrecision($user_id, $type)
  {
    $repTable = $this->getReputationTable();
    $reps = $repTable->getRepByUseridAndType($user_id, $type);

    $likeCnt = 0;

    if (count($reps) == 0) { return null; }

    for ($i=0; $i<count($reps); $i++) {
      $realRep = floatval($reps[$i]['reputation']);
      if ($realRep >= 4.0) { $likeCnt++; }
    }

    return $likeCnt / count($reps);
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
        'rank' => $row->rank,
        'expected' => $row->expected,
        'type' => $row->type,
      );
    }

    return $data;
  }
}
