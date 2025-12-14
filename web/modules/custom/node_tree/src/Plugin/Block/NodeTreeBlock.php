<?php

namespace Drupal\node_tree\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node_tree\Controller\NodeTreeController;
use Drupal\Component\DependencyInjection\ContainerInterface;
/* web/core/lib/Drupal/Core/Plugin/ContainerFactoryPluginInterface.php */


/**
 * Provides hierarchical node_tree taxon tree block.
 *
 * @Block(
 *  id = "node_treeTaxonTreeBlock",
 *  admin_label = @Translation("node_tree hierarchical taxon tree"),
 * )
 */
class node_treeTaxonTreeBlock extends BlockBase implements ContainerFactoryPluginInterface {

protected NodeTreeController $contentController;

// In MyContentBlock.php
  /** 
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      // Method 'Drupal\node_tree\Plugin\Block\node_treeTaxonTreeBlock::create()' is not compatible with method 'Drupal\Core\Plugin\ContainerFactoryPluginInterface::create()'.intelephense(P1038)
      // REALLY?!
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('node_tree.NodeTreeController')
    );
  }

// In MyContentBlock.php
// The type-hint for the fourth argument is crucial for telling PHP
// what kind of object is expected.
public function __construct(array $configuration, $plugin_id, $plugin_definition, NodeTreeController $content_controller) {
  // 1. Call the parent constructor first (standard practice for plugins).
  parent::__construct($configuration, $plugin_id, $plugin_definition);

  // 2. Assign the controller object that was passed in to the
  //    protected property $this->contentController.
  $this->contentController = $content_controller;
}


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [] + parent::defaultConfiguration();
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    // https://www.drupal.org/docs/8/creating-custom-modules/creating-custom-blocks/create-a-custom-block#s-note-using-twig-templates-with-custom-blocks

    $taxanotesoutput = '';

// $render_array = $this->contentController->blockContent();


    $renderable = [
      '#theme' => 'node-tree-hierarchy-tree-block',
      '#attached' => array(
        'library' => array(
          'node_tree/node_treetree',
        ),
      ),
      '#taxanotesoutput' => $taxanotesoutput,
    ];



    /*
    $renderable = [
      '#theme' => 'node-tree-hierarchy-tree-block',
      '#taxanotesoutput' => $taxanotesoutput,
    ];
    */

    // where corresponding theme file would be:
// web/modules/custom/taxanotes/templates/node-tree-hierarchy-tree-block.html.twig

    /*

        // https://stackoverflow.com/a/48787759/227926
        //
        // also: https://www.drupal.org/forum/support/module-development-and-code-questions/2016-08-08/adding-js-to-the-block-through-module
        // and: https://www.drupal.org/docs/theming-drupal/adding-stylesheets-css-and-javascript-js-to-a-drupal-theme

        // could be useful: https://drupal.stackexchange.com/questions/197007/how-to-properly-add-inline-javascript
        // https://drupal.stackexchange.com/questions/95635/adding-js-to-a-drupal-8-theme-replacement-for-drupal-add-js
      
    return [
  '#theme' => 'your_module_theme_id',
  '#someVariable' => $some_variable,
  '#attached' => array(
    'library' => array(
      'your_module/library_name',
    ),
  ),
];

    */

    return $renderable;
  }

/**
   * {@inheritdoc}
   *
   * By default, BlockBase implements per-user caching.
   * If your content is truly dynamic and should not be cached, you can
   * override getCacheMaxAge() or merge cacheability metadata from the controller.
   */
  public function getCacheMaxAge() {
    // Return 0 to prevent caching for dynamic content.
    return 0;

    // Alternatively, for cacheable content:
    // return parent::getCacheMaxAge();

      // https://claude.ai/public/artifacts/812234ad-8586-4172-b0bd-62cb3f25aac4
  }

  // taxon equivalent
}