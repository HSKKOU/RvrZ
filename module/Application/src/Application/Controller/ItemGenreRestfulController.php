<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

use Application\Model\ItemGenreModel;

class ItemGenreRestfulController extends AbstractRvrController
{
  protected $itemGenreTable;

  public function getItemGenreTable()
  {
    if(!$this->itemGenreTable)
    {
      $sm = $this->getServiceLocator();
      $this->itemGenreTable = $sm->get('Application\Model\ItemGenreModelTable');
    }

    return $this->itemGenreTable;
  }

  public function getList()
  {
    return $this->makeSuccessJson($this->getListRaw());
  }

  public function get($id)
  {
    // if ($id == 'createIdTree') {
    //   $this->createIdTree();
    //   return $this->makeSuccessJson('create id tree');
    // }

    $gotModel = $this->getItemGenreTable()->getItemGenre($id);
    return $this->makeSuccessJson(array(
      'id' => $gotModel->id,
      'genre_name' => $gotModel->genre_name,
      'parent_genre_id' => $gotModel->parent_genre_id,
      'id_tree' => $gotModel->id_tree,
    ));
  }

  public function create($data)
  {
    $newModel = new ItemGenreModel();
    $newModel->exchangeArray($data);
    $result = $this->getItemGenreTable()->saveItemGenre($newModel);
    $savedData = array();
    if ($result == 1) {
      $fetchList = $this->getListRaw();
      $savedData = $fetchList[count($fetchList)-1];
    }

    return $this->makeSuccessJson($savedData);
  }



  private function createIdTree()
  {
    $itemGenres = $this->getItemGenreTable()->fetchAll();
    foreach ($itemGenres as $ig) {
      $igId = $ig->id;
      if ($igId == 0) { continue; }
      $idTreeStr = "";
      while (true) {
        $pId = $this->getParentId($igId);
        if ($pId < 0) { break; }
        $idTreeStr = ($pId . "," . $idTreeStr);
        $igId = $pId;
      }
      $ig->id_tree = substr($idTreeStr, 0, strlen($idTreeStr)-1);

      $this->getItemGenreTable()->saveItemGenre($ig);
    }
  }
  private function getParentId($id)
  {
    if ($id < 0) { return -1; }
    $ig = $this->getItemGenreTable()->getItemGenre($id);
    return intval($ig->parent_genre_id);
  }





  /* Utilities */
  public function getListRaw()
  {
    $data = array();

    $rowSet = $this->getItemGenreTable()->fetchAll();
    foreach ($rowSet as $row) {
      $data[] = array(
        'id' => $row->id,
        'genre_name' => $row->genre_name,
        'parent_genre_id' => $row->parent_genre_id,
        'id_tree' => $row->id_tree,
      );
    }

    return $data;
  }
}
