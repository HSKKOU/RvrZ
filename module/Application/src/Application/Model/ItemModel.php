<?php
namespace Application\Model;

class ItemModel
{
  public $id;
  public $item_code;
  public $name;
  public $price;
  public $description;
  public $url_item;
  public $url_image;
  public $review_num;
  public $review_avg;
  public $genre_id;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->item_code = (isset($data['item_code']))? $data['item_code']:NULL;
    $this->name = (isset($data['name']))? $data['name']:NULL;
    $this->price = (isset($data['price']))? $data['price']:NULL;
    $this->description = (isset($data['description']))? $data['description']:NULL;
    $this->url_item = (isset($data['url_item']))? $data['url_item']:NULL;
    $this->url_image = (isset($data['url_image']))? $data['url_image']:NULL;
    $this->review_num = (isset($data['review_num']))? $data['review_num']:0;
    $this->review_avg = (isset($data['review_avg']))? $data['review_avg']:NULL;
    $this->genre_id = (isset($data['genre_id']))? $data['genre_id']:NULL;
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'item_code' => $this->item_code,
      'name' => $this->name,
      'price' => $this->price,
      'description' => $this->description,
      'url_item' => $this->url_item,
      'url_image' => $this->url_image,
      'review_num' => $this->review_num,
      'review_avg' => $this->review_avg,
      'genre_id' => $this->genre_id,
    );
  }
}
