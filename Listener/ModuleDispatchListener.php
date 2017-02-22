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

namespace Zikula\RoutesModule\Listener;

use Zikula\RoutesModule\Listener\Base\AbstractModuleDispatchListener;
use Zikula\Core\Event\GenericEvent;

/**
 * Event handler implementation class for dispatching modules.
 */
class ModuleDispatchListener extends AbstractModuleDispatchListener
{
    /**
     * Makes our handlers known to the event system.
     */
    public static function getSubscribedEvents()
    {
        return parent::getSubscribedEvents();
    }
    
    /**
     * {@inheritdoc}
     */
    public function serviceLinks(GenericEvent $event)
    {
        parent::customClassName($event);
    
        // Inject router and translator services and format data like this:
        // $event->data[] = [
        //     'url' => $router->generate('zikularoutesmodule_user_index'),
        //     'text' => $translator->__('Link text')
        // ];
    
        // you can access general data available in the event
        
        // the event name
        // echo 'Event: ' . $event->getName();
        
        // type of current request: MASTER_REQUEST or SUB_REQUEST
        // if a listener should only be active for the master request,
        // be sure to check that at the beginning of your method
        // if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
        //     // don't do anything if it's not the master request
        //     return;
        // }
        
        // kernel instance handling the current request
        // $kernel = $event->getKernel();
        
        // the currently handled request
        // $request = $event->getRequest();
    }
}
