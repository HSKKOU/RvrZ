<?php
namespace Application\Model;

class ReputationModel
{
  public $id;
  public $user_id;
  public $item_id;
  public $reputation;
  public $rank;
  public $expected;
  public $type;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->user_id = (isset($data['user_id']))? $data['user_id']:0;
    $this->item_id = (isset($data['item_id']))? $data['item_id']:0;
    $this->reputation = (isset($data['reputation']))? $data['reputation']:0;
    $this->rank = (isset($data['rank']))? $data['rank']:0;
    $this->expected = (isset($data['expected']))? $data['expected']:0;
    $this->type = (isset($data['type']))? $data['type']:'all';
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'user_id' => $this->user_id,
      'item_id' => $this->item_id,
      'reputation' => $this->reputation,
      'rank' => $this->rank,
      'expected' => $this->expected,
      'type' => $this->type,
    );
  }
}
