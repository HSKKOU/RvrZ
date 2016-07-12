<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class TestModelTable
{
  protected $tableGateway;

  public function __construct(TableGateway $tableGateway)
  {
    $this->tableGateway = $tableGateway;
  }

  public function fetchAll()
  {
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }

  public function getTestModel($id)
  {
    $id = (int)$id;
    $rowSet = $this->tableGateway->select(array('id' => $id));
    $row = $rowSet->current();
    if(!$row) {
      throw new \Exception("Coul not find roe $id");
    }

    return $row;
  }

  public function saveTestModel(TestModel $testModel)
  {
    $data = array(
      'text' => $testModel->text,
    );

    $id = (int)$testModel->id;
    if ($id == 0) {
      return $this->tableGateway->insert($data);
    } else {
      if ($this->getTestModel($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        return new \Exception("TestModel id dose not exist");
      }
    }
  }

  public function deleteTestMoel($id)
  {
    return $this->tableGateway->delete(array('id' => (int)$id));
  }
}
