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

namespace Zikula\RoutesModule\Listener\Base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;

/**
 * Event handler base class for dispatching modules.
 */
abstract class AbstractModuleDispatchListener implements EventSubscriberInterface
{
    /**
     * Makes our handlers known to the event system.
     */
    public static function getSubscribedEvents()
    {
        return [
            'module_dispatch.postloadgeneric'  => ['postLoadGeneric', 5],
            'module_dispatch.preexecute'       => ['preExecute', 5],
            'module_dispatch.postexecute'      => ['postExecute', 5],
            'module_dispatch.custom_classname' => ['customClassname', 5],
            'module_dispatch.service_links'    => ['serviceLinks', 5]
        ];
    }
    
    /**
     * Listener for the `module_dispatch.postloadgeneric` event.
     *
     * Called after a module api or controller has been loaded.
     * Receives the args `['modinfo' => $modinfo, 'type' => $type, 'force' => $force, 'api' => $api]`.
     *
     * @param GenericEvent $event The event instance
     */
    public function postLoadGeneric(GenericEvent $event)
    {
    }
    
    /**
     * Listener for the `module_dispatch.preexecute` event.
     *
     * Occurs in `ModUtil::exec()` before function call with the following args:
     *     `[
     *          'modname' => $modname,
     *          'modfunc' => $modfunc,
     *          'args' => $args,
     *          'modinfo' => $modinfo,
     *          'type' => $type,
     *          'api' => $api
     *      ]`
     * .
     *
     * @param GenericEvent $event The event instance
     */
    public function preExecute(GenericEvent $event)
    {
    }
    
    /**
     * Listener for the `module_dispatch.postexecute` event.
     *
     * Occurs in `ModUtil::exec()` after function call with the following args:
     *     `[
     *          'modname' => $modname,
     *          'modfunc' => $modfunc,
     *          'args' => $args,
     *          'modinfo' => $modinfo,
     *          'type' => $type,
     *          'api' => $api
     *      ]`
     * .
     * Receives the modules output with `$event->getData();`.
     * Can modify this output with `$event->setData($data);`.
     *
     * @param GenericEvent $event The event instance
     */
    public function postExecute(GenericEvent $event)
    {
    }
    
    /**
     * Listener for the `module_dispatch.custom_classname` event.
     *
     * In order to override the classname calculated in `ModUtil::exec()`.
     * In order to override a pre-existing controller/api method, use this event type to override the class name that is loaded.
     * This allows to override the methods using inheritance.
     * Receives no subject, args of `['modname' => $modname, 'modinfo' => $modinfo, 'type' => $type, 'api' => $api]`
     * and 'event data' of `$className`. This can be altered by setting `$event->setData()` followed by `$event->stopPropagation()`.
     *
     * @param GenericEvent $event The event instance
     */
    public function customClassname(GenericEvent $event)
    {
    }
    
    /**
     * Listener for the `module_dispatch.service_links` event.
     *
     * Occurs when building admin menu items.
     * Adds sublinks to a Services menu that is appended to all modules if populated.
     * Triggered by module_dispatch.postexecute in bootstrap.
     *
     * @param GenericEvent $event The event instance
     */
    public function serviceLinks(GenericEvent $event)
    {
    }
}
