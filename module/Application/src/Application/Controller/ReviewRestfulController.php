<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ReviewModel;
use Application\Model\ReviewModelTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class ReviewRestfulController extends AbstractRvrController
{
  protected $reviewTable;

  public function indexAction()
  {
    return new ViewModel();
  }

  public function getReviewTable()
  {
    if(!$this->reviewTable)
    {
      $sm = $this->getServiceLocator();
      $this->reviewTable = $sm->get('Application\Model\ReviewModelTable');
    }

    return $this->reviewTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
    if ($id == 'createDummy') {
      $this->createDummyReviews(100);
      return $this->makeSuccessJson('created Dummies');
    } else if ($id == 'reflectReviews') {
      $this->reflectReviewsToItemTable();
      return $this->makeSuccessJson('reflected reviews');
    } else if (preg_match('/removeOldReviewsBySameUser/', $id)) {
      $this->removeOldReviewsBySameUser($id);
      return $this->makeSuccessJson('remove reviews by same users');
    }

    $gotModel = $this->getReviewTable()->getReview($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'user_name' => $gotModel->user_name,
      'item_id' => $gotModel->item_id,
      'item_genre_id' => $gotModel->item_genre_id,
      'point' => $gotModel->point,
    ));
  }

  private function createDummyReviews($_userNum)
  {
    $items = $this->getServiceLocator()->get('Application\Model\ItemModelTable')->fetchAll();
    $maxReviewNum = count($items) / 2;
    for ($ui=0; $ui<$_userNum; $ui++) {
      $itemIds = array();
      for ($c=0; $c<count($items); $c++) { $itemIds[] = $c+1; }
      for ($c=0; $c<$maxReviewNum; $c++) {
        $in = rand(0, count($itemIds)-1);

        $review = array(
          'user_name' => 'User'.$ui,
          'item_id' => $itemIds[$in],
          'point' => rand(1,5),
        );
        $this->create($review);

        unset($itemIds[$in]);
        $itemIds = array_values($itemIds);
      }
    }
  }

  private function reflectReviewsToItemTable()
  {
    $itemTable = $this->getServiceLocator()->get('Application\Model\ItemModelTable');
    $reviewTable = $this->getReviewTable();

    $items = $itemTable->fetchAll();
    foreach ($items as $ik => $iv) {
      $reviews = $reviewTable->getReviewsByItemId(+$iv->id);
      $pointAve = 0.0;
      $reviewCnt = 0;
      if (count($reviews) != 0) {
        foreach ($reviews as $rk => $review) {
          $pointAve += floatval($review->point);
          $reviewCnt++;
        }
        $pointAve /= floatval($reviewCnt);
        $pointAve = round($pointAve, 2);
      }
      $iv->review_num = $reviewCnt;
      $iv->review_avg = $pointAve;
      $itemTable->saveItem($iv);
    }
  }

  private function removeOldReviewsBySameUser($_idStr)
  {
    $db_id_split = explode("_", $_idStr);
    $db_id = $db_id_split[1];

    $sm = $this->getServiceLocator();

    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    $resultSetPrototype = new ResultSet();
    $resultSetPrototype->setArrayObjectPrototype(new ReviewModel());

    $rts = array();
    for ($i=0; $i<36; $i++) {
      $rn = substr('0'.($i+1), -2, 2);
      $reviewTable = new ReviewModelTable(new TableGateway('reviews_'.$rn, $dbAdapter, null, $resultSetPrototype));
      $rts[] = $reviewTable;
    }

    $ri = +$db_id;

    // $reviews = $rts[$ri]->fetchAll();
    for ($rId=0; $rId<2000000; $rId++) {
      try {
        $rts[$ri]->getReview($rId);
      } catch (\Exception $e) {
        continue;
      }

      $rUserName = $review->user_name;
      $rItemId = $review->item_id;

      $newest_review;
      $newest_rt_key;
      $is_found = false;
      for ($j=0; $j<36; $j++) {
        if ($ri == $j) { continue; }
        $matchRevs = $rts[$j]->getReviewsByItemIdAndUserName($rItemId, $rUserName);
        if (count($matchRevs) == 0) {
          unset($matchRevs);
          continue;
        }
        $newest_review = $matchRevs[count($matchRevs)-1];
        $newest_rt_key = $j;
        $is_found = true;
        unset($matchRevs);
      }

      if (!$is_found) { continue; }

      for ($j=0; $j<36; $j++) {
        if ($j == $newest_rt_key) { continue; }
        $rts[$j]->deleteReviewsByItemIdAndUserName($rItemId, $rUserName);
      }

      unset($newest_review);
      unset($matchRevs);
    }
  }








  public function create($data)
  {
    $newModel = new ReviewModel();
    $newModel->exchangeArray($data);
    $result = $this->getReviewTable()->saveReview($newModel);
    $savedData = array();
    if ($result == 1) {
      $fetchList = $this->getListRaw();
      $savedData = $fetchList[count($fetchList)-1];
    }

    return $this->makeJson($result, $newModel);
  }

  public function update($id, $data)
  {
    $newModel = new ReviewModel();
    $newModel->exchangeArray($data);
    $newModel->id = $id;
    $result = $this->getReviewTable()->saveReview($newModel);
    $savedData = array();
    if ($result == 1) {
      $savedData = $this->get($id);
    }

    return $this->makeJson($result, $newModel);
  }

  public function delete($id)
  {
    $delModel = $this->getReviewTable()->getReview($id);
    $result = $this->getReviewTable()->deleteReview($id);

    return $this->makeJson($result, $delModel->exchangeToArray());
  }






  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getReviewTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'user_name' => $row->user_name,
        'item_id' => $row->item_id,
        'item_genre_id' => $row->item_genre_id,
        'point' => $row->point,
      );
    }

    return $data;
  }
}
