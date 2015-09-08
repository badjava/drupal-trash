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
  
  public function defaultRedirect() {
    return $this->redirect('trash.entity_list', ['entity' => $this->defaultEntity()]);
  }
  
  public function entityList($entity = NULL) {
    $results = $this->entityQuery->get($entity)
            ->isDeleted()
            ->execute();
    $entities = entity_load_multiple_deleted('node', $results);
    
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
        $url = Url::fromUri('http://www.example.com/');
        $links = [
          'restore' => [
            'title' => 'Restore', 
            'url' => $url,
          ],
          'purge' => [
            'title' => 'Purge', 
            'url' => $url,
          ],
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
          'changed' => \Drupal::service('date.formatter')->format($entity->getChangedTimeAcrossTranslations(), 'short'),
          'operations' => [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ],
        );
      }
    }
    
    return array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No entities available.'),
    );
  }
  
  private function defaultEntity(){
    if (\Drupal::moduleHandler()->moduleExists('node')) {
      return 'node';
    }
  }
}