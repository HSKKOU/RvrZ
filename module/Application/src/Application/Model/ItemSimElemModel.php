<?php
namespace Application\Model;

class ItemSimElemModel
{
  public $item1_code;
  public $item2_code;
  public $i1Sum;
  public $i2Sum;
  public $i1P2Sum;
  public $i2P2Sum;
  public $i1xi2Sum;
  public $num;

  public function exchangeArray($data)
  {
    $this->item1_code = (isset($data['item1_code']))? $data['item1_code']:'';
    $this->item2_code = (isset($data['item2_code']))? $data['item2_code']:'';
    $this->i1Sum = (isset($data['i1Sum']))? $data['i1Sum']:0;
    $this->i2Sum = (isset($data['i2Sum']))? $data['i2Sum']:0;
    $this->i1P2Sum = (isset($data['i1P2Sum']))? $data['i1P2Sum']:0;
    $this->i2P2Sum = (isset($data['i2P2Sum']))? $data['i2P2Sum']:0;
    $this->i1xi2Sum = (isset($data['i1xi2Sum']))? $data['i1xi2Sum']:0;
    $this->num = (isset($data['num']))? $data['num']:0;
  }

  public function exchangeToArray()
  {
    return array(
      'item1_code' => $this->item1_code,
      'item2_code' => $this->item2_code,
      'i1Sum' => $this->i1Sum,
      'i2Sum' => $this->i2Sum,
      'i1P2Sum' => $this->i1P2Sum,
      'i2P2Sum' => $this->i2P2Sum,
      'i1xi2Sum' => $this->i1xi2Sum,
      'num' => $this->num,
    );
  }
}
