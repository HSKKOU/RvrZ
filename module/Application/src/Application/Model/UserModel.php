<?php
namespace Application\Model;

class UserModel
{
  public $id;
  public $name;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->name = (isset($data['name']))? $data['name']:"User None";
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'name' => $this->name,
    );
  }
}
