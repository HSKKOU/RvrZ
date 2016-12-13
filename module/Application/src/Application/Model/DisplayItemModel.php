<?php
namespace Application\Model;

class DisplayItemModel
{
  public $id;
  public $user_id;
  public $item_id;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->user_id = (isset($data['user_id']))? $data['user_id']:0;
    $this->item_id = (isset($data['item_id']))? $data['item_id']:0;
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'user_id' => $this->user_id,
      'item_id' => $this->item_id,
    );
  }
}
