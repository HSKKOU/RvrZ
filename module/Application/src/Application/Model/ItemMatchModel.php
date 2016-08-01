<?php
namespace Application\Model;

class ItemMatchModel
{
  public $item_id;
  public $matched_item_id;
  public $similarity;

  public function exchangeArray($data)
  {
    $this->item_id = (isset($data['item_id']))? $data['item_id']:0;
    $this->matched_item_id = (isset($data['matched_item_id']))? $data['matched_item_id']:0;
    $this->similarity = (isset($data['similarity']))? $data['similarity']:0;
  }

  public function exchangeToArray()
  {
    return array(
      'item_id' => $this->item_id,
      'matched_item_id' => $this->matched_item_id,
      'similarity' => $this->similarity,
    );
  }
}
