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
  public $gaze_at_time;
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
    $this->gaze_at_time = (isset($data['gaze_at_time']))? $data['gaze_at_time']:NULL;

    if (isset($data['distance'])) {
      $this->distance = $data['distance'];
    } else if ($this->user_positionV3 == NULL || $this->gaze_item_positionV3 == NULL) {
      $this->distance = NULL;
    } else {
      $sqSum = 0;
      $userPosSp = explode(',', $this->user_positionV3);
      $itemPosSp = explode(',', $this->gaze_item_positionV3);
      for ($i=0; $i<=2; $i++) { $sqSum += pow((intval($userPosSp[$i]) - intval($itemPosSp[$i])), 2); }
      $this->distance = sqrt($sqSum);
    }

    $this->time = (isset($data['time']))? $data['time']:"0";
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
      'gaze_at_time' => $this->gaze_at_time,
      'distance' => $this->distance,
      'time' => $this->time,
    );
  }
}
