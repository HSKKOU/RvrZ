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
    $rowSet = $this->tableGateway->select(array('item_id' => $item_id));

    $retReviews = array();
    while ($row = $rowSet->current()) {
      $retReviews[] = $row;
      $rowSet->next();
    }

    return $retReviews;
  }

  public function getReviewsByItemIdAndUserName($item_id, $user_name)
  {
    $rowSet = $this->tableGateway->select(array('item_id' => $item_id, 'user_name' => $user_name));

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
      'item_id' => $reviewModel->item_id,
      'item_genre_id' => $reviewModel->item_genre_id,
      'point' => $reviewModel->point,
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

  public function deleteReviewsByItemIdAndUserName($item_id, $user_name)
  {
    $this->tableGateway->delete(array('item_id' => $item_id, 'user_name' => $user_name));
  }
}
