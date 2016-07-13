<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Container\Base;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\RoutesModule\Helper\ControllerHelper;

/**
 * This is the link container service implementation class.
 */
class LinkContainer implements LinkContainerInterface
{
    use TranslatorTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var PermissionApi
     */
    protected $permissionApi;

    /**
     * @var ControllerHelper
     */
    protected $controllerHelper;

    /**
     * Constructor.
     * Initialises member vars.
     *
     * @param TranslatorInterface $translator       Translator service instance.
     * @param Routerinterface     $router           Router service instance.
     * @param PermissionApi       $permissionApi    PermissionApi service instance.
     * @param ControllerHelper    $controllerHelper ControllerHelper service instance.
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router, PermissionApi $permissionApi, ControllerHelper $controllerHelper)
    {
        $this->setTranslator($translator);
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->controllerHelper = $controllerHelper;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance.
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * Returns available header links.
     *
     * @param string $type The type to collect links for.
     *
     * @return array Array of header links.
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $utilArgs = ['api' => 'linkContainer', 'action' => 'getLinks'];
        $allowedObjectTypes = $this->controllerHelper->getObjectTypes('api', $utilArgs);

        $permLevel = LinkContainerInterface::TYPE_ADMIN == $type ? ACCESS_ADMIN : ACCESS_READ;

        // Create an array of links to return
        $links = [];

        
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            
            if (in_array('route', $allowedObjectTypes)
                && $this->permissionApi->hasPermission($this->getBundleName() . ':Route:', '::', $permLevel)) {
                $links[] = [
                    'url' => $this->router->generate('zikularoutesmodule_route_adminview'),
                     'text' => $this->__('Routes'),
                     'title' => $this->__('Route list')
                 ];
            }
        }

        return $links;
    }

    public function getBundleName()
    {
        return 'ZikulaRoutesModule';
    }
}
