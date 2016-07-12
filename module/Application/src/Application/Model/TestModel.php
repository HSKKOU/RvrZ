<?php
namespace Application\Model;

class TestModel
{
  public $id;
  public $text;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->text = (isset($data['text']))? $data['text']:'';
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'text' => $this->text,
    );
  }
}
