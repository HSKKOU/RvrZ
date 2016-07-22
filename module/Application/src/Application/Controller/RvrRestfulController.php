<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemModel;

class RvrRestfulController extends AbstractRvrController
{
  protected $itemTable;
  protected $reviewTable;
  protected $inputsTable;

  public function indexAction() { return new ViewModel(); }



  /* get db tables reference */
  public function getItemTable() {
    if(!$this->itemTable) { $this->itemTable = $this->getServiceLocator()->get('Application\Model\ItemModelTable'); }
    return $this->itemTable;
  }
  public function getReviewTable() {
    if(!$this->reviewTable) { $this->reviewTable = $this->getServiceLocator()->get('Application\Model\ReviewModelTable'); }
    return $this->reviewTable;
  }
  public function getInputsTable() {
    if(!$this->inputsTable) { $this->inputsTable = $this->getServiceLocator()->get('Application\Model\InputsModelTable'); }
    return $this->inputsTable;
  }
  /* end get db tables reference */




  /* Restful API methods */
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
    ));
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

    return $this->makeJson($result, $newModel);
  }
  /* end Restful API methods */




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
  /* end Utilities */


}
