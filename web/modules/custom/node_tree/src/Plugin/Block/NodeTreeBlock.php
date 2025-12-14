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
 *  id = "NodeTreeBlock",
 *  admin_label = @Translation("node_tree hierarchical taxon tree"),
 * )
 */
class NodeTreeBlock extends BlockBase {



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
      '#title' => $this->t('Hierarchy'),
      '#items' => [],
      '#attached' => array(
        'library' => array(
          'node_tree/node_tree',
        ),
      ),
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