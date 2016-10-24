<?php
namespace Application\Model;

class ReviewModel
{
  public $id;
  public $user_name;
  public $item_id;
  public $item_genre_id;
  public $point;


  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->user_name = (isset($data['user_name']))? $data['user_name']:NULL;
    $this->item_id = (isset($data['item_id']))? $data['item_id']:NULL;
    $this->item_genre_id = (isset($data['item_genre_id']))? $data['item_genre_id']:NULL;
    $this->point = (isset($data['point']))? $data['point']:NULL;
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'user_name' => $this->user_name,
      'item_id' => $this->item_id,
      'item_genre_id' => $this->item_genre_id,
      'point' => $this->point,
    );
  }
}
