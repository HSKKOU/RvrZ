<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class ReviewUserModelTable
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

  public function getReviewUser($user_name)
  {
    $rowSet = $this->tableGateway->select(array('user_name' => $user_name));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Could not find row $user_name");
    }

    return $row;
  }

  public function saveReviewUser(ReviewUserModel $reviewUserModel)
  {
    $data = array(
      'user_name' => $reviewUserModel->user_name,
      'reviews' => $reviewUserModel->reviews,
    );

    $user_name = $reviewUserModel->user_name;
    if ($user_name == '') {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getReviewUser($user_name)) {
        $this->tableGateway->update($data, array('user_name' => $user_name));
      } else {
        return new \Exception("ReviewUserModel user_name dose not exist");
      }
    }
  }

  public function deleteReviewUser($user_name)
  {
    return $this->tableGateway->delete(array('user_name' => $user_name));
  }


  public function getLastData()
  {
    return $this->tableGateway->lastInsertValue;
  }
}
