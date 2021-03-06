<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;
use Application\Model\ItemMatchModel;
use Application\Model\ItemGenreModel;
use Application\Model\DisplayItemModel;

class ItemRestfulController extends AbstractRvrController
{
  protected $itemTable;
  protected $itemMatchesTable;
  protected $itemGenreTable;
  protected $displayItemTable;

  private $genreIds = array(
    // 100005, // 花・ガーデン・DIY
    // 100026, // パソコン・周辺機器
    100227, // 食品
    // 100316, // 水・ソフトドリンク
    100804, // インテリア・寝具・収納
    101070, // スポーツ・アウトドア
    551177, // メンズファッション
    // 558885, // 靴
  );

  // get Table
  public function getItemTable() {
    if(!$this->itemTable) { $this->itemTable = $this->getServiceLocator()->get('Application\Model\ItemModelTable'); }
    return $this->itemTable;
  }
  public function getItemMatchTable() {
    if(!$this->itemMatchesTable) { $this->itemMatchesTable = $this->getServiceLocator()->get('Application\Model\ItemMatchModelTable'); }
    return $this->itemMatchesTable;
  }
  public function getItemGenreTable() {
    if(!$this->itemGenreTable) { $this->itemGenreTable = $this->getServiceLocator()->get('Application\Model\ItemGenreModelTable'); }
    return $this->itemGenreTable;
  }
  public function getDisplayItemTable() {
    if(!$this->displayItemTable) { $this->displayItemTable = $this->getServiceLocator()->get('Application\Model\DisplayItemModelTable'); }
    return $this->displayItemTable;
  }
  // end get Tables

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
    if (preg_match('/random/', $id)) {
      $rndSp = explode("_", $id);
      $rndNum = 10;
      if (isset($rndSp[1]) && is_numeric($rndSp[1])) { $rndNum = +$rndSp[1]; }
      $igt = $this->getItemGenreTable();
      $relatedGenres = $igt->getItemGenreRelated($this->genreIds);

      // $result = array();
      // for ($i=0; $i<$rndNum; $i++) {
      //   $item = new ItemModel();
      //   $item->id = $i+1;
      //   $item->name = "Item".($i+1);
      //   $item->url_image = "/img/index.png";
      // }
      // return $this->makeSuccessJson($result);

      $rndItems = $this->getItemTable()->getItemsByRandom($rndNum, $relatedGenres);
      if (isset($rndSp[2]) && is_numeric($rndSp[2])) {
        $userId = +$rndSp[2];
        $diTable = $this->getDisplayItemTable();
        foreach ($rndItems as $ik => $iv) {
          $di = new DisplayItemModel();
          $di->user_id = $userId;
          $di->item_id = +$iv["id"];
          $diTable->saveDisplayItem($di);
        }
      }
      return $this->makeSuccessJson($rndItems);
    } else if (preg_match('/import/', $id)) {
      $result = $this->importItemData($id);
      return $this->makeSuccessJson($result);
    }
    $gotModel = $this->getItemTable()->getItem($id);
    return $this->makeSuccessJson(array(
        'id' => $gotModel->id,
        'item_code' => $gotModel->item_code,
        'name' => $gotModel->name,
        'price' => $gotModel->price,
        'description' => $gotModel->description,
        'url_item' => $gotModel->url_item,
        'url_image' => $gotModel->url_image,
        'review_num' => $gotModel->review_num,
        'review_avg' => $gotModel->review_avg,
        'genre_id' => $gotModel->genre_id,
      )
    );
  }














  public function create($data)
  {
    $newModel = new ItemModel();
    $newModel->exchangeArray($data);
    $result = $this->getItemTable()->saveItem($newModel);
    $savedData = array();
    if ($result == 1) {
      $fetchList = $this->getListRaw();
      $savedData = $fetchList[count($fetchList)-1];
    }

    return $this->makeSuccessJson($newModel);
  }

  public function update($id, $data)
  {
    $newModel = new ItemModel();
    $newModel->exchangeArray($data);
    $newModel->id = $id;
    $result = $this->getItemTable()->saveItem($newModel);
    $savedData = array();
    if ($result == 1) {
      $savedData = $this->get($id);
    }

    return $this->makeSuccessJson($newModel);
  }

  public function delete($id)
  {
    $delModel = $this->getItemTable()->getItem($id);
    $result = $this->getItemTable()->deleteItem($id);

    return $this->makeSuccessJson($delModel->exchangeToArray());
  }






  private function importItemData($idStr)
  {
    $file_id_split = explode("_", $idStr);
    $file_id = $file_id_split[1];
    $cnt = $this->importItemDataForEachFile($file_id);

    return "success import item data[" . $file_id . "] : " . $cnt . "records";
  }

  private function importItemDataForEachFile($file_number)
  {
    $fnStr = "00".$file_number;
    $fnStr = substr($fnStr, -3);
    $file_path = "F:\items\ichiba01_item" . $fnStr . "_20140221.tsv";
    if (!($fp = fopen($file_path, "r"))) { return "cannot open file"; }

    $filesize = filesize($file_path);
    $cnt = 0;
    $size = 0;

    $output = "";

    while (!feof($fp)) {
      // if ($cnt > 10) { break; }
      $line = fgets($fp);
      $size = strlen(trim($line));

      $output = explode("\t", $line);

      if ($output[6] == 'http://image.rakuten.co.jp/interiorkataoka/cabinet/top/ct-elure.jpg') { continue; }
      if (preg_match('/noimage/', $output[6])) { continue; }
      if ($output[7] == '0' || $output[8] == '0.00') { continue; }

      $cnt++;

      $itemInfo = array(
        'name' => $output[0],
        'item_code' => $output[1],
        'price' => $output[2],
        'description' => $output[3],
        'url_item' => $output[5],
        'url_image' => $output[6],
        'review_num' => $output[7],
        'review_avg' => $output[8],
        'genre_id' => $output[10],
      );
      $this->create($itemInfo);
    }

    fclose($fp);

    return $cnt;
  }





  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getItemTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'item_code' => $row->item_code,
        'name' => $row->name,
        'price' => $row->price,
        'description' => $row->description,
        'url_item' => $row->url_item,
        'url_image' => $row->url_image,
        'review_num' => $row->review_num,
        'review_avg' => $row->review_avg,
        'genre_id' => $row->genre_id,
      );
    }

    return $data;
  }
}
