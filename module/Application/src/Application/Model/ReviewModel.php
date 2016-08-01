<?php
namespace Application\Model;

class ReviewModel
{
  public $id;
  public $user_name;
  public $user_age;
  public $user_sex;
  public $item_id;
  public $item_name;
  public $store_name;
  public $url_item;
  public $item_genre_id;
  public $item_price;
  public $purchase_flag;
  public $content;
  public $objective;
  public $frequency;
  public $point;
  public $review_title;
  public $review_content;
  public $review_date;


  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->user_name = (isset($data['user_name']))? $data['user_name']:NULL;
    $this->user_age = (isset($data['user_age']))? $data['user_age']:NULL;
    $this->user_sex = (isset($data['user_sex']))? $data['user_sex']:NULL;
    $this->item_id = (isset($data['item_id']))? $data['item_id']:NULL;
    $this->item_name = (isset($data['item_name']))? $data['item_name']:NULL;
    $this->store_name = (isset($data['store_name']))? $data['store_name']:NULL;
    $this->url_item = (isset($data['url_item']))? $data['url_item']:NULL;
    $this->item_genre_id = (isset($data['item_genre_id']))? $data['item_genre_id']:NULL;
    $this->item_price = (isset($data['item_price']))? $data['item_price']:NULL;
    $this->purchase_flag = (isset($data['purchase_flag']))? $data['purchase_flag']:NULL;
    $this->content = (isset($data['content']))? $data['content']:NULL;
    $this->objective = (isset($data['objective']))? $data['objective']:NULL;
    $this->frequency = (isset($data['frequency']))? $data['frequency']:NULL;
    $this->point = (isset($data['point']))? $data['point']:NULL;
    $this->review_title = (isset($data['review_title']))? $data['review_title']:NULL;
    $this->review_content = (isset($data['review_content']))? $data['review_content']:NULL;
    $this->review_date = (isset($data['review_date']))? $data['review_date']:NULL;
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'user_name' => $this->user_name,
      'user_age' => $this->user_age,
      'user_sex' => $this->user_sex,
      'item_id' => $this->item_id,
      'item_name' => $this->item_name,
      'store_name' => $this->store_name,
      'url_item' => $this->url_item,
      'item_genre_id' => $this->item_genre_id,
      'item_price' => $this->item_price,
      'purchase_flag' => $this->purchase_flag,
      'content' => $this->content,
      'objective' => $this->objective,
      'frequency' => $this->frequency,
      'point' => $this->point,
      'review_title' => $this->review_title,
      'review_content' => $this->review_content,
      'review_date' => $this->review_date,
    );
  }
}
