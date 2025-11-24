<?php

namespace Drupal\node_tree\Controller;
use Drupal\Core\Entity\Query\QueryFactory;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
//const NODE_TREE_NODE_ROOT_guid = ''; // TODO - fill value
const NODE_TREE_NODE_ROOT_guid = 'a'; // TODO - fill value
// but how do I deal with when I have multiple roots?

/**
 * 
 * 
 */
class NodeTreeController extends ControllerBase {
  public function responseForImmediatechildrenOfParent() {
    return new JsonResponse($this->getImmediatechildrenOfParent());
  }
  

  /**
   * 
   * example:
   */
  public function responseForGetNodeContent( $guid ) {
    $node = null;

    $guidToUse = '';
    if ( !$this->IsNullOrEmptyString($guid) ) {
      $guidToUse = $guid;
    }
    else {
      $guidToUse = NODE_TREE_NODE_ROOT_guid;
    }
    $this->getNodeFromguid( $guidToUse, $node );

    // https://drupal.stackexchange.com/a/194368/1082
    // and
    // https://drupal.stackexchange.com/questions/178434/how-do-i-render-nodes-now-that-node-view-has-been-deprecated#comment395002_194368
    //
    $entity_type = 'node';
    $view_mode = 'full';  
    
    //https://www.drupal.org/node/2939099
    //$nodeHtml = (render(\Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($node, $view_mode)))->__toString();
    //
    // now becomes:
    $build = \Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($node, $view_mode);
    $output = \Drupal::service('renderer')->render($build);
    $nodeHtml = $output->__toString();
    
    return new JsonResponse([
      'data' => $nodeHtml,
      'method' => 'GET',
    ]);
  }



  /**
   * 
   */
  // https://taxanotes.ddev.site/node_tree/api/getImmediateChildrenOfParent/?node=<value>
  // very subtle /?node= works but not ?node= without the preceding slash
  //public function getImmediatechildrenOfParent() {
  public function getImmediatechildrenOfParent($aNode) {

    // https://gemini.google.com/share/c1a91d550221



// 1. Get the Entity Query service.
$query = \Drupal::entityQuery('node');

// 2. Define the conditions:
//    - Filter by content type (optional but recommended for performance/scope).
//$query->condition('type', 'referencing_content_type');
$query->condition('type', 'taxon');

//    - Crucially, filter the entity reference field's target_id
//      to match the ID of the node being referenced.
$field_name = 'field_parent_guid';
// https://gemini.google.com/share/efb010b3e331
$target_nid = $aNode->id();
$query->condition($field_name, $target_nid);

// 3. Execute the query to get an array of NIDs (Node IDs).
$referencing_nids = $query->execute();

// 4. Load the full node objects (optional, but often needed).
$referencing_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($referencing_nids);

// $referencing_nodes now contains all the nodes that reference the target node.


      foreach ($referencing_nodes as $nid => $node) {
        $guid = $node->get('field_guid')->getValue();
        $childObj = (object) [
          'name' => $node->label(),
          'id' => $guid,
          //'id' => $node->guid(),
          'load_on_demand' => true
        ];
        
        $child_node_ids_array[] = $childObj;
      }


    
    //https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Field%21EntityReferenceFieldItemList.php/function/EntityReferenceFieldItemList%3A%3AreferencedEntities/11.x

    //$anArray = $aNode->get('')->referencedEntities();

    
    
    // parent node_tree node id
    //$parent_guid = $aNode->id();
    // as per spec: https://mbraak.github.io/jqTree/examples/05_load_on_demand/


    /*
    $query = \Drupal::request()->query;
    $parent_guid = $query->get('node');

    $child_node_ids_array = [];
    if ( !$this->IsNullOrEmptyString( $parent_guid ) ) {


  
      // https://drupal.stackexchange.com/a/280924/1082

      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
       'type' => 'node_tree',
       'field_parent_guid' => $parent_guid,
      ]);

      foreach ($nodes as $nid => $node) {
        $childObj = (object) [
          'name' => $node->label(),
          'id' => $node->guid(),
          'load_on_demand' => true
        ];
        
        $child_node_ids_array[] = $childObj;
      }
    }
    else {

      // the if-then needs to decide if a node guid has been passed in or not, and if not, set one, as in the top level parent
      // then feed this into a query that gets the children
      //
      // might want to use IsNullOrEmptyString
      // and getNodeFromTaxonkey 

      $entity = null;
      $rootTaxonKey = NODE_TREE_NODE_ROOT_guid;

      $this->getNodeFromguid( $rootTaxonKey, $entity );

      //$entity = \Drupal::service('entity.repository')->loadEntityByguid('node', node_tree_NODE_ROOT_guid);
      $name = $entity->getTitle();
      $childObj = (object) [
        'name' => $name,
        'id' => $entity->guid(),
        'load_on_demand' => true
      ];

      $child_node_ids_array[] = $childObj;
    }
    */
    
    return $child_node_ids_array;
  }

  /**
   * 
   * 
   * example:
   */
  public function getExpandedTreePathToNode( $guid ) {
    $startingGuid = '';
    $expandedTreePathAsArray = [];
    if ( !$this->IsNullOrEmptyString( $guid ) ) {
      $startingGuid = $guid;
    }
    else {
      $startingGuid = NODE_TREE_NODE_ROOT_guid;
    }

    // get the node that matches this taxon key
    //
    // there should be only one node for a given guid, so the array $nodes should have one element only

    // get the parent using the reference field and its parent and so on

    $startingParentGuid = '';
    $startingParentTaxonName = '';
    $node = null;
    $this->getNodeFromGuid( $startingGuid, $node );

    if ($node != null ) {
      $this->addTreeNode( $startingGuid, $node->getTitle(), $expandedTreePathAsArray );

      // if node has a parent then continue walking up the branch of the tree
      $this->getImmediateParentInfo( $startingGuid, $startingParentTaxonName, $startingParentGuid );
      if ( !$this->IsNullOrEmptyString($startingParentGuid ) ) {
        $this->growExpandedTreePath( $expandedTreePathAsArray, $startingParentTaxonName, $startingParentGuid );
      }
      // else node doesn't have parent so we're already at the top node and don't need to construct a container for its parent to put it in
    }


    return new JsonResponse($expandedTreePathAsArray);
  }

  private function growExpandedTreePath( &$pathAsArray, $name, $guidOfCurrentNodeInPath ) {



    //$pathAsArray[] = $taxonKeyOfCurrentNodeInPath;

    // get the node's parent guid and add it to the array

    $nextParentTaxonName = '';
    $nextParentguid = '';
    $this->getImmediateParentInfo( $guidOfCurrentNodeInPath, $nextParentTaxonName, $nextParentguid );

    if ( $this->IsNullOrEmptyString(  $nextParentguid ) ) {
      // stopping case for recursion
      $this->addTreeNode( $guidOfCurrentNodeInPath, $name, $pathAsArray );
      return; // we are at the top of the tree
    }
    else {
      $this->addTreeNode( $guidOfCurrentNodeInPath, $name, $pathAsArray );
      $this->growExpandedTreePath( $pathAsArray, $nextParentTaxonName, $nextParentguid );
    }
  }

  private function getNodeFromTaxonKey( $taxonkey, &$node ) {
    $nodeQueryAsArray = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
      'type' => 'node_tree',
      'field_taxon_key' => $taxonkey,
    ]);

    // only expect one element array, because taxon key should be unique
    foreach ($nodeQueryAsArray as $nid => $aNode) {
      $node = $aNode; 
    }
  }


  /**
   * 
   * guid is a taxonkey, but NOT the taxonkey field!  this has made it confusing - there is a guid for the node and there is a taxonkey - why both?!
   */
  private function getNodeFromguid( $guid, &$node ) {
    // https://gemini.google.com/share/07f07dfe0efe



//use Drupal\Core\Entity\Query\QueryFactory;

/** @var \Drupal\Core\Entity\Query\QueryFactory $entity_query */
$entity_query = \Drupal::service('entity.query');

// Build the query for nodes.
$query = $entity_query->get('node')
  // 1. Filter by content type (bundle). This is optional but highly recommended for performance.
  ->condition('type', 'taxon')
  
  // 2. Filter by field value.
  //    - 'parent_guid' is the machine name of your field.
  //    - 'value' is the column name for simple fields (text, integer, boolean, etc.).
  //    - 'Your Search Value' is the value you are trying to match.
  
  //->condition('field', 'Your Search Value')
  ->condition('parent_guid', $guid)
  
  // 3. (Optional) Filter for published nodes.
  ->condition('status', 1)
  
  // 4. (Optional) Limit the number of results.
  ->range(0, 1) // Get only the first match
  ;

// Execute the query.
$nids = $query->execute();

// Check if any nodes were found.
if (!empty($nids)) {
  // Get the first Node ID (nid).
  $nid = reset($nids); 
  
  // Load the full node object.
  /** @var \Drupal\node\NodeInterface $node */
  $node = \Drupal\node\Entity\Node::load($nid);
  
  // Now you have the node object.
  // ... your logic here ...
} else {
  // No node found matching the criteria.
}


/*
    // https://drupal.stackexchange.com/a/291869/1082
    $node = \Drupal::service('entity.repository')->loadEntityByguid('node', $guid);
    // TODO
*/
  }


  private function getImmediateParentInfo( $childguid, &$parentName, &$parentguid ) {
    $parentguid = '';
    $parentName = '';

    $childNode = null;
    $this->getNodeFromguid( $childguid, $childNode );
    // should be just one node, but we get back an array to iterate through in any case

    // only expect one element array, because taxon key should be unique
    // foreach ($nodeQueryAsArray as $nid => $node) {
      # https://drupal.stackexchange.com/questions/144947/how-do-i-access-a-field-value-for-an-entity-e-g-node-object

    if ( $childNode ) {
      $parentNodeInfoAsArray = $childNode->get('field_parent_guid')->getValue();
      // target_guid isn't the guid of the parent

      $parentName = '';
      if ( array_key_exists( 'target_id', $parentNodeInfoAsArray[0] ) ) {
      
        $parentNodeId = $parentNodeInfoAsArray[0]['target_id'];

        if ( $this->IsNullOrEmptyString( $parentNodeId ) ) {
          //break; // this node has no parent, so we're at the top of the tree, which we'd reach eventually!
        }
        else {
          // https://drupal.stackexchange.com/a/256326/1082
          $parentNode = \Drupal\node\Entity\Node::load($parentNodeId);

          // wrong! field_taxon_key is not guid
          //$parentguid = $parentNode->get('field_taxon_key')->getValue()[0]['value'];
          //$parentguid = $parentNode->guid();
          $parentNode->get('field_guid')->getValue();

          $parentName = $parentNode->getTitle();
        }


      }
    }
  //  }
  }

  // https://stackoverflow.com/a/381275/227926
  private function IsNullOrEmptyString($str){
    return ($str === null || trim($str) === '');
  }

  // tree so far 
  private function addTreeNode( $guid, $name, &$tree ) {
    if ( count( $tree ) > 0 ) {

      $treePrevious = &$tree;
 
      $tree = 
        [
          (object) [
            'id' => $guid,
            'name' => $name,
            'is_open' => true, 
            "is_loading" => false,
            'children' => $treePrevious // (already in an array I think so no need for additional [] )
          ]
        ];
    }
    else {
      // brand new tree
      $tree = [
          (object) [
            'id' => $guid,
           'name' => $name,
           'is_selected' => true,
           'is_closed' => false, 
          ]
      ];
    }
  }
}
