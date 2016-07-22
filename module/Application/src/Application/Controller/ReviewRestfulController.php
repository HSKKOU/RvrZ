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
    $gotModel = $this->getReviewTable()->getReview($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'user_name' => $gotModel->user_name,
      'user_age' => $gotModel->user_age,
      'user_sex' => $gotModel->user_sex,
      'item_code' => $gotModel->item_code,
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
        'item_code' => $row->item_code,
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
