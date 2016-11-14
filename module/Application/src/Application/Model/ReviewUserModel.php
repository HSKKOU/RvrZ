<?php
namespace Application\Model;

class ReviewUserModel
{
  public $user_name;
  public $reviews;

  public function exchangeArray($data)
  {
    $this->user_name = (isset($data['user_name']))? $data['user_name']:'';
    $this->reviews = (isset($data['reviews']))? $data['reviews']:0;
  }

  public function exchangeToArray()
  {
    return array(
      'user_name' => $this->user_name,
      'reviews' => $this->reviews,
    );
  }
}
