<?php

/**
 * @file
 * Contains \Drupal\trash\Controller\TrashController.
 */

namespace Drupal\trash\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

class TrashController extends ControllerBase {

  /**
   * The entity query object.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  
  /**
   * Constructs an TrashController object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query object.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }
  
  public function entityList() {
    $results = $this->entityQuery->get('node')
            ->isDeleted()
            ->execute();
    $entities = entity_load_multiple_deleted('node', $results);
    return array('#markup' => print_r($entities, true));
  }
}