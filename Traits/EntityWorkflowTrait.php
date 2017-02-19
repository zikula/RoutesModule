<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.3 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Traits;

use ServiceUtil;
use Zikula_Workflow_Util;

/**
 * Workflow trait implementation class.
 */
trait EntityWorkflowTrait
{
    /**
     * @var array The current workflow data of this object
     */
    protected $__WORKFLOW__ = [];
    
    /**
     * Returns the __ w o r k f l o w__.
     *
     * @return array
     */
    public function get__WORKFLOW__()
    {
        return $this->__WORKFLOW__;
    }
    
    /**
     * Sets the __ w o r k f l o w__.
     *
     * @param array $__WORKFLOW__
     *
     * @return void
     */
    public function set__WORKFLOW__($__WORKFLOW__ = [])
    {
        $this->__WORKFLOW__ = $__WORKFLOW__;
    }
    
    /**
     * Returns the name of the primary identifier field.
     * For entities with composite keys the first identifier field is used.
     *
     * @return string Identifier field name
     */
    public function getWorkflowIdColumn()
    {
        $entityClass = 'ZikulaRoutesModule:' . ucfirst($this->get_objectType()) . 'Entity';
    
        $entityManager = ServiceUtil::get('doctrine.orm.default_entity_manager');
        $meta = $entityManager->getClassMetadata($entityClass);
    
        return $meta->getSingleIdentifierFieldName();
    }
    
    /**
     * Sets/retrieves the workflow details.
     *
     * @param boolean $forceLoading load the workflow record
     *
     * @throws RuntimeException Thrown if retrieving the workflow object fails
     */
    public function initWorkflow($forceLoading = false)
    {
        $request = ServiceUtil::get('request_stack')->getCurrentRequest();
        $routeName = $request->get('_route');
    
        $loadingRequired = false !== strpos($routeName, 'edit') || false !== strpos($routeName, 'delete');
        $isReuse = $request->query->getBoolean('astemplate', false);
    
        $container = ServiceUtil::get('service_container');
        $translator = $container->get('translator.default');
        $workflowHelper = $container->get('zikula_routes_module.workflow_helper');
        
        $objectType = $this->get_objectType();
        $idColumn = $this->getWorkflowIdColumn();
        
        // apply workflow with most important information
        $schemaName = $workflowHelper->getWorkflowName($objectType);
        $this['__WORKFLOW__'] = [
            'module' => 'ZikulaRoutesModule',
            'state' => $this->getWorkflowState(),
            'obj_table' => $objectType,
            'obj_idcolumn' => $idColumn,
            'obj_id' => $this[$idColumn],
            'schemaname' => $schemaName
        ];
        
        // load the real workflow only when required (e. g. when func is edit or delete)
        if (($loadingRequired && !$isReuse) || $forceLoading) {
            $result = Zikula_Workflow_Util::getWorkflowForObject($this, $objectType, $idColumn, 'ZikulaRoutesModule');
            if (!$result) {
                $flashBag = $container->get('session')->getFlashBag();
                $flashBag->add('error', $translator->__('Error! Could not load the associated workflow.'));
            }
        }
        
        if (!is_object($this['__WORKFLOW__']) && !isset($this['__WORKFLOW__']['schemaname'])) {
            $workflow = $this['__WORKFLOW__'];
            $workflow['schemaname'] = $schemaName;
            $this['__WORKFLOW__'] = $workflow;
        }
    }
    
    /**
     * Resets workflow data back to initial state.
     * This is for example used during cloning an entity object.
     */
    public function resetWorkflow()
    {
        $this->setWorkflowState('initial');
    
        $workflowHelper = ServiceUtil::get('zikula_routes_module.workflow_helper');
    
        $schemaName = $workflowHelper->getWorkflowName($this->get_objectType());
        $this['__WORKFLOW__'] = [
            'module' => 'ZikulaRoutesModule',
            'state' => $this->getWorkflowState(),
            'obj_table' => $this->get_objectType(),
            'obj_idcolumn' => $this->getWorkflowIdColumn(),
            'obj_id' => 0,
            'schemaname' => $schemaName
        ];
    }
    
}
