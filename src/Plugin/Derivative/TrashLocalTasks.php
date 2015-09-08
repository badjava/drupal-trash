<?php
/**
 * @file
 * Contains \Drupal\trash\Plugin\Derivative\DynamicLocalTasks.
 */

namespace Drupal\trash\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\multiversion\MultiversionManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks.
 */
class TrashLocalTasks extends DeriverBase implements ContainerDeriverInterface {
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;
  
  /**
   * Constructs a TrashLocalTasks object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
    $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $manager = \Drupal::service('multiversion.manager');
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($manager->isSupportedEntityType($entity_type)) {
        $this->derivatives["trash_$entity_type_id"] = $base_plugin_definition;
        $this->derivatives["trash_$entity_type_id"]['title'] = $entity_type->get('label');
        $this->derivatives["trash_$entity_type_id"]['route_name'] = 'trash.entity_list';
        $this->derivatives["trash_$entity_type_id"]['base_route'] = 'trash.default';
        $this->derivatives["trash_$entity_type_id"]['route_parameters'] = array('entity' => $entity_type_id);
      }
    }
    return $this->derivatives;
  }

}
