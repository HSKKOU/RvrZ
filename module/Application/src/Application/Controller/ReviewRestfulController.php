<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ReviewModel;

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
