<?php
namespace Application\Model;

class UserModel
{
  public $id;
  public $name;
  public $created_at;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->name = (isset($data['name']))? $data['name']:"User None";
    $this->created_at = (isset($data['created_at']))? $data['created_at']:"";
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'name' => $this->name,
      'created_at' => $this->created_at,
    );
  }
}
