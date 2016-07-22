<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;

class ItemRestfulController extends AbstractRvrController
{
  protected $itemTable;

  public function getItemTable()
  {
    if(!$this->itemTable)
    {
      $sm = $this->getServiceLocator();
      $this->itemTable = $sm->get('Application\Model\ItemModelTable');
    }

    return $this->itemTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
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
