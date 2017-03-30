<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.4 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Entity\Factory\Base;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;

/**
 * Factory class used to create entities and receive entity repositories.
 *
 * This is the base factory class.
 */
abstract class AbstractRoutesFactory
{
    /**
     * @var ObjectManager The object manager to be used for determining the repository
     */
    protected $objectManager;

    /**
     * RoutesFactory constructor.
     *
     * @param ObjectManager $objectManager The object manager to be used for determining the repositories
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns a repository for a given object type.
     *
     * @param string $objectType Name of desired entity type
     *
     * @return EntityRepository The repository responsible for the given object type
     */
    public function getRepository($objectType)
    {
        $entityClass = 'Zikula\\RoutesModule\\Entity\\' . ucfirst($objectType) . 'Entity';

        return $this->objectManager->getRepository($entityClass);
    }

    /**
     * Creates a new route instance.
     *
     * @return Zikula\RoutesModule\Entity\routeEntity The newly created entity instance
     */
    public function createRoute()
    {
        $entityClass = 'Zikula\\RoutesModule\\Entity\\RouteEntity';

        return new $entityClass();
    }

    /**
     * Gets the list of identifier fields for a given object type.
     *
     * @param string $objectType The object type to be treated
     *
     * @return array List of identifier field names
     */
    public function getIdFields($objectType = '')
    {
        if (empty($objectType)) {
            throw new InvalidArgumentException('Invalid object type received.');
        }
        $entityClass = 'ZikulaRoutesModule:' . ucfirst($objectType) . 'Entity';
    
        $meta = $this->entityFactory->getObjectManager()->getClassMetadata($entityClass);
    
        if ($this->hasCompositeKeys($objectType)) {
            $idFields = $meta->getIdentifierFieldNames();
        } else {
            $idFields = [$meta->getSingleIdentifierFieldName()];
        }
    
        return $idFields;
    }

    /**
     * Checks whether a certain entity type uses composite keys or not.
     *
     * @param string $objectType The object type to retrieve
     *
     * @return Boolean Whether composite keys are used or not
     */
    public function hasCompositeKeys($objectType)
    {
        return false;
    }

    /**
     * Returns the object manager.
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
    
    /**
     * Sets the object manager.
     *
     * @param ObjectManager $objectManager
     *
     * @return void
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = $objectManager;
    }
    
}
