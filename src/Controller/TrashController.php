<?php

/**
 * @file
 * Contains \Drupal\trash\Controller\TrashController.
 */

namespace Drupal\trash\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\multiversion\MultiversionManagerInterface;

class TrashController extends ControllerBase {

  /**
   * The entity query object.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The entity manager service.
   *
   * @var \Drupal\multiversion\MultiversionManagerInterface
   */
  protected $multiversionManager;

  /**
   * Constructs an TrashController object.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query object.
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *    The date formatter service.
   * @param \Drupal\multiversion\MultiversionManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(QueryFactory $entity_query, DateFormatter $date_formatter, multiversionManagerInterface $multiversion_manager) {
    $this->entityQuery = $entity_query;
    $this->dateFormatter = $date_formatter;
    $this->multiversionManager = $multiversion_manager;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('date.formatter'),
      $container->get('multiversion.manager')
    );
  }
  
  public function summary() {
    $items = [];
    foreach ($this->multiversionManager->getSupportedEntityTypes() as $entity_type_id => $entity_type) {
      $entities = $this->loadEntities($entity_type_id);
      $items[$entity_type_id] = [
        '#type' => 'link',
        '#title' => $entity_type->get('label') . ' (' . count($entities) . ')', 
        '#url' => Url::fromRoute('trash.entity_list', ['entity_type_id' => $entity_type->id()]),
      ];
    }
    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => 'Trash bins'
    ];
  }
  
  public function getTitle($entity = NULL) {
    if (!empty($entity)) {
      $entity_types = $this->multiversionManager->getSupportedEntityTypes();
      return $entity_types[$entity]->get('label') . ' trash';
    }
    else {
      return 'Trash';
    }
  }
  
  public function entityList($entity_type_id = NULL) {

    $entities = $this->loadEntities($entity_type_id);

    $header = array(
      'id' => t('Id'),
      'name' => t('name'),
      'changed' => array(
        'data' => $this->t('Updated'),
        'specifier' => 'changed',
        'sort' => 'desc',
        'class' => array(RESPONSIVE_PRIORITY_LOW),
      ),
      'operations' => t('Operations'),
    );
    
    $rows = [];
    
    foreach ($entities as $entity) {
      if ($entity instanceof \Drupal\Core\Entity\EntityInterface) {
        $links = [
          'restore' => [
            'title' => 'Restore', 
            'url' => Url::fromRoute('restore.form', ['entity' => $entity->getEntityTypeId(), 'id' => $entity->id()]),
          ],
          //'purge' => [
          //  'title' => 'Purge', 
          //  'url' => Url::fromRoute('purge.form', ['entity' => $entity->getEntityTypeId(), 'id' => $entity->id()]),
          //],
        ];
        $rows[] = array(
          'id' => $entity->id(),
          'label' => [
            'data' => [
              '#type' => 'link',
              '#title' => $entity->label(),
              '#access' => $entity->access('view'),
              '#url' => $entity->urlInfo(),
            ],
          ],
          'changed' => $this->dateFormatter->format($entity->getChangedTimeAcrossTranslations(), 'short'),
          'operations' => [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ],
        );
      }
    }
    
    $entity_types = $this->multiversionManager->getSupportedEntityTypes();
    return array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('The @label trash is empty.', ['@label' => $entity_types[$entity_type_id]->get('label')]),
    );
  }
  
  private function loadEntities($entity = null) {
    if (!empty($entity)) {
      $results = $this->entityQuery->get($entity)
              ->isDeleted()
              ->execute();
      return entity_load_multiple_deleted($entity, $results);
    }
  }
}
