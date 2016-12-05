<?php
namespace Application\Model;

class ItemGenreModel
{
  public $id;
  public $genre_name;
  public $parent_genre_id;
  public $id_tree;
  public $second_top;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:-1;
    $this->genre_name = (isset($data['genre_name']))? $data['genre_name']:"";
    $this->parent_genre_id = (isset($data['parent_genre_id']))? $data['parent_genre_id']:-1;
    $this->id_tree = (isset($data['id_tree']))? $data['id_tree']:"";
    $this->second_top = (isset($data['second_top']))? $data['second_top']:0;
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'genre_name' => $this->genre_name,
      'parent_genre_id' => $this->parent_genre_id,
      'id_tree' => $this->id_tree,
      'second_top' => $this->second_top,
    );
  }
}
