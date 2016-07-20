<?php
namespace Application\Utilities;

function str2Date($_str) {

}

function calcDateStrDelta($_ds1, $ds2) {
  $retDelta = 0;

  $ds1Sp = explode('.', $_ds1);
  $ds2Sp = explode('.', $_ds2);
  $date1 = str2Date($ds1Sp[0]);
  $date2 = str2Date($ds2Sp[0]);
  $diff = $date1->diff($date2, true);

  $retDelta += $diff->s;
  if ($retDelta->i == 0) { return $retDelta; }
  $retDelta += $diff->i;

  return $retDelta; 
}

function str2date($_str) {
  return new DateTime(substr($_str, 0, 4).'-'.substr($_str, 4, 2).'-'.substr($_str, 6, 2).' '.substr($_str, 8, 2).':'.substr($_str, 10, 2).':'.substr($_str, 12, 2));
}
