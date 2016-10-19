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
    } else if (preg_match('/import/', $id)) {
      $result = $this->importReviewData($id);
      return $this->makeSuccessJson($result);
    }

    $gotModel = $this->getReviewTable()->getReview($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'user_name' => $gotModel->user_name,
      'user_age' => $gotModel->user_age,
      'user_sex' => $gotModel->user_sex,
      'item_id' => $gotModel->item_id,
      'item_name' => $gotModel->item_name,
      'store_name' => $gotModel->store_name,
      'url_item' => $gotModel->url_item,
      'item_genre_id' => $gotModel->item_genre_id,
      'item_price' => $gotModel->item_price,
      'purchase_flag' => $gotModel->purchase_flag,
      'content' => $gotModel->content,
      'objective' => $gotModel->objective,
      'frequency' => $gotModel->frequency,
      'point' => $gotModel->point,
      'review_title' => $gotModel->review_title,
      'review_content' => $gotModel->review_content,
      'review_date' => $gotModel->review_date,
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





  private function importReviewData($idStr)
  {
    $file_id_split = explode("_", $idStr);
    $file_id = $file_id_split[1];
    $cnt = $this->importReviewDataForEachFile($file_id);

    return "success import item data[" . $file_id . "] : " . $cnt . "records";
  }

  private function importReviewDataForEachFile($file_number)
  {
    $fnStr = "0".$file_number;
    $fnStr = substr($fnStr, -2);
    $file_path = "F:\\reviews\ichiba04_review2012" . $fnStr . "_20140221.tsv";
    if (!($fp = fopen($file_path, "r"))) { return "cannot open file"; }

    $filesize = filesize($file_path);
    $cnt = 0;
    $size = 0;

    $output = "";

    while (!feof($fp)) {
      // if ($cnt > 10) { break; }
      $line = fgets($fp);
      $size = strlen(trim($line));

      $output = explode("\t", $line);

      $cnt++;

      $user_sex = 2;
      if ($output[0] != "購入者さん") { $user_sex = $output[2]; }

      $reviewInfo = array(
        'user_name' => $output[0],
        'user_age' => $output[1],
        'user_sex' => $user_sex,
        'item_id' => $output[3],
        'item_name' => $output[4],
        'store_name' => $output[5],
        'url_item' => $output[6],
        'item_genre_id' => $output[7],
        'item_price' => $output[8],
        'purchase_flag' => $output[9],
        'content' => $output[10],
        'objective' => $output[11],
        'frequency' => $output[12],
        'point' => $output[13],
        'review_title' => $output[14],
        'review_content' => $output[15],
        'review_date' => $output[16],
      );
      $this->create($reviewInfo);
    }

    fclose($fp);

    return $cnt;
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
        'user_age' => $row->user_age,
        'user_sex' => $row->user_sex,
        'item_id' => $row->item_id,
        'item_name' => $row->item_name,
        'store_name' => $row->store_name,
        'url_item' => $row->url_item,
        'item_genre_id' => $row->item_genre_id,
        'item_price' => $row->item_price,
        'purchase_flag' => $row->purchase_flag,
        'content' => $row->content,
        'objective' => $row->objective,
        'frequency' => $row->frequency,
        'point' => $row->point,
        'review_title' => $row->review_title,
        'review_content' => $row->review_content,
        'review_date' => $row->review_date,
      );
    }

    return $data;
  }
}
