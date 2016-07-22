<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\TestModel;

class TestRestfulController extends AbstractRvrController
{
  protected $testModelTable;

  public function indexAction()
  {
    return new ViewModel();
  }

  public function getTestModelTable()
  {
    if(!$this->testModelTable)
    {
      $sm = $this->getServiceLocator();
      $this->testModelTable = $sm->get('Application\Model\TestModelTable');
    }

    return $this->testModelTable;
  }

  public function getList()
  {
    return new JsonModel($this->getListRaw());
  }

  public function get($id)
  {
    $gotModel = $this->getTestModelTable()->getTestModel($id);
    return new JsonModel(array(
      "id" => $gotModel->id,
      "text" => $gotModel->text,
    ));
  }

  public function create($data)
  {
    $newModel = new TestModel();
    $newModel->exchangeArray($data);
    $result = $this->getTestModelTable()->saveTestModel($newModel);
    $savedData = array();
    if ($result == 1) {
      $fetchList = $this->getListRaw();
      $savedData = $fetchList[count($fetchList)-1];
    }

    return new JsonModel(array(
      'result' => $result,
      'data' => $newModel,
    ));
  }

  public function update($id, $data)
  {
    $newModel = new TestModel();
    $newModel->exchangeArray($data);
    $newModel->id = $id;
    $result = $this->getTestModelTable()->saveTestModel($newModel);
    $savedData = array();
    if ($result == 1) {
      $savedData = $this->get($id);
    }

    return new JsonModel(array(
      'result' => $result,
      'data' => $newModel,
    ));
  }

  public function delete($id)
  {
    $delModel = $this->getTestModelTable()->getTestModel($id);
    $result = $this->getTestModelTable()->deleteTestMoel($id);

    return new JsonModel(array(
      'result' => $result,
      'data' => $delModel->exchangeToArray(),
    ));
  }






  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getTestModelTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'text' => $row->text,
      );
    }

    return $data;
  }
}
