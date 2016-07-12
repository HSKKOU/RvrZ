<?php
namespace Application\Model;

class InputsModel
{
  public $id;
  public $user_id;
  public $user_positionV3;
  public $gaze_item_id;
  public $gaze_item_positionV3;
  public $gaze_pointV2;
  public $around_item_ids;
  public $distance;
  public $time;

  public function exchangeArray($data)
  {
    $this->id = (isset($data['id']))? $data['id']:0;
    $this->user_id = (isset($data['user_id']))? $data['user_id']:NULL;
    $this->user_positionV3 = (isset($data['user_positionV3']))? $data['user_positionV3']:NULL;
    $this->gaze_item_id = (isset($data['gaze_item_id']))? $data['gaze_item_id']:NULL;
    $this->gaze_item_positionV3 = (isset($data['gaze_item_positionV3']))? $data['gaze_item_positionV3']:NULL;
    $this->gaze_pointV2 = (isset($data['gaze_pointV2']))? $data['gaze_pointV2']:NULL;
    $this->around_item_ids = (isset($data['around_item_ids']))? $data['around_item_ids']:NULL;
    $this->distance = (isset($data['distance']))? $data['distance']:NULL;
    $this->time = (isset($data['time']))? $data['time']:NULL;
  }

  public function exchangeToArray()
  {
    return array(
      'id' => $this->id,
      'user_id' => $this->user_id,
      'user_positionV3' => $this->user_positionV3,
      'gaze_item_id' => $this->gaze_item_id,
      'gaze_item_positionV3' => $this->gaze_item_positionV3,
      'gaze_pointV2' => $this->gaze_pointV2,
      'around_item_ids' => $this->around_item_ids,
      'distance' => $this->distance,
      'time' => $this->time,
    );
  }
}
