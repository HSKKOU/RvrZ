<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;
use Application\Model\ItemMatchModel;

class ItemRestfulController extends AbstractRvrController
{
  protected $itemTable;
  protected $itemMatchesTable;

  // get Table
  public function getItemTable() {
    if(!$this->itemTable) { $this->itemTable = $this->getServiceLocator()->get('Application\Model\ItemModelTable'); }
    return $this->itemTable;
  }
  public function getItemMatchTable() {
    if(!$this->itemMatchesTable) { $this->itemMatchesTable = $this->getServiceLocator()->get('Application\Model\ItemMatchModelTable'); }
    return $this->itemMatchesTable;
  }
  // end get Tables

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
    if ($id == 'updateMatch') {
      $this->updateItemSimilarTable();
      return $this->makeSuccessJson('update Item Matches Table');
    }

    $gotModel = $this->getItemTable()->getItem($id);
    return $this->makeSuccessJson(array(
        'id' => $gotModel->id,
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



  private function updateItemSimilarTable()
  {
    $it = $this->getItemTable();
    $imt = $this->getItemMatchTable();

    $items = $it->fetchAll()->buffer();
    $items2 = $it->fetchAll()->buffer();

    foreach ($items as $item) {
      foreach ($items2 as $item2) {
        if ($item->id >= $item2->id) { continue; }
        $s = $this->calcItemSimilarity();
        $ima = array(
          'item_id' => $item->id,
          'matched_item_id' => $item2->id,
          'similarity' => $s,
        );
        $im = new ItemMatchModel();
        $im->exchangeArray($ima);
        $imt->saveItemMatch($im);
      }
    }
  }

  private function calcItemSimilarity()
  {
    // TODO: implement dataset dreation
    return rand(1, 10000) / 11000;
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






  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getItemTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
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
