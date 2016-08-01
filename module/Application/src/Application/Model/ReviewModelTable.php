<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ReviewModelTable
{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll()
  {
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }

  public function getReview($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find roe $id");
    }

    return $row;
  }

  public function getReviewsByItemId($item_id)
  {
    $item_id = (int)$item_id;
    $rowSet = $this->tableGateway->select(array('item_id' => $item_id));

    $retReviews = array();
    while ($row = $rowSet->current()) {
      $retReviews[] = $row;
      $rowSet->next();
    }

    return $retReviews;
  }

  public function saveReview(ReviewModel $reviewModel)
  {
    $data = array(
      'user_name' => $reviewModel->user_name,
      'user_age' => $reviewModel->user_age,
      'user_sex' => $reviewModel->user_sex,
      'item_id' => $reviewModel->item_id,
      'item_name' => $reviewModel->item_name,
      'store_name' => $reviewModel->store_name,
      'url_item' => $reviewModel->url_item,
      'item_genre_id' => $reviewModel->item_genre_id,
      'item_price' => $reviewModel->item_price,
      'purchase_flag' => $reviewModel->purchase_flag,
      'content' => $reviewModel->content,
      'objective' => $reviewModel->objective,
      'frequency' => $reviewModel->frequency,
      'point' => $reviewModel->point,
      'review_title' => $reviewModel->review_title,
      'review_content' => $reviewModel->review_content,
      'review_date' => $reviewModel->review_date,
    );

    $id = (int)$reviewModel->id;
    if ($id == 0) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getReview($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("ReviewModel id dose not exist");
      }
    }
  }

  public function deleteReview($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }
}
