<?php

declare(strict_types=1);

namespace Drupal\node_tree\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a nodetreeblock block.
 */
#[Block(
  id: 'node_tree_nodetreeblock',
  admin_label: new TranslatableMarkup('NodeTreeBlock'),
  category: new TranslatableMarkup('Custom'),
)]
final class NodeTreeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    /*
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    */
    return [
      '#theme' => 'node_tree_nodetreeblock',
      '#data' => [
        'title' => $this->t('Hierarchy'),
      ],
      '#cache' => [
        'max-age' => 3600,
      ],
    ];
  }

}
