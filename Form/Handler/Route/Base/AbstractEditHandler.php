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

namespace Zikula\RoutesModule\Form\Handler\Route\Base;

use Zikula\RoutesModule\Form\Handler\Common\EditHandler;
use Zikula\RoutesModule\Form\Type\RouteType;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use RuntimeException;

/**
 * This handler class handles the page events of editing forms.
 * It aims on the route object type.
 */
abstract class AbstractEditHandler extends EditHandler
{
    /**
     * Initialise form handler.
     *
     * This method takes care of all necessary initialisation of our data and form states.
     *
     * @param array $templateParameters List of preassigned template variables
     *
     * @return boolean False in case of initialisation errors, otherwise true
     */
    public function processForm(array $templateParameters)
    {
        $this->objectType = 'route';
        $this->objectTypeCapital = 'Route';
        $this->objectTypeLower = 'route';
        
        $this->hasPageLockSupport = true;
    
        $result = parent::processForm($templateParameters);
        if ($result instanceof RedirectResponse) {
            return $result;
        }
    
        if ($this->templateParameters['mode'] == 'create') {
            if (!$this->modelHelper->canBeCreated($this->objectType)) {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Sorry, but you can not create the route yet as other items are required which must be created before!'));
                $logArgs = ['app' => 'ZikulaRoutesModule', 'user' => $this->currentUserApi->get('uname'), 'entity' => $this->objectType];
                $this->logger->notice('{app}: User {user} tried to create a new {entity}, but failed as it other items are required which must be created before.', $logArgs);
    
                return new RedirectResponse($this->getRedirectUrl(['commandName' => '']), 302);
            }
        }
    
        $entityData = $this->entityRef->toArray();
    
        // assign data to template as array (for additions like standard fields)
        $this->templateParameters[$this->objectTypeLower] = $entityData;
    
        return $result;
    }
    
    /**
     * Creates the form type.
     */
    protected function createForm()
    {
        $options = [
            'mode' => $this->templateParameters['mode'],
            'actions' => $this->templateParameters['actions'],
            'has_moderate_permission' => $this->permissionApi->hasPermission($this->permissionComponent, $this->idValue . '::', ACCESS_MODERATE),
        ];
    
        return $this->formFactory->create(RouteType::class, $this->entityRef, $options);
    }


    /**
     * Get list of allowed redirect codes.
     *
     * @return array list of possible redirect codes
     */
    protected function getRedirectCodes()
    {
        $codes = parent::getRedirectCodes();
    
        // user index page of route area
        $codes[] = 'userIndex';
        // admin index page of route area
        $codes[] = 'adminIndex';
        // user list of routes
        $codes[] = 'userView';
        // admin list of routes
        $codes[] = 'adminView';
        // user list of own routes
        $codes[] = 'userOwnView';
        // admin list of own routes
        $codes[] = 'adminOwnView';
        // user detail page of treated route
        $codes[] = 'userDisplay';
        // admin detail page of treated route
        $codes[] = 'adminDisplay';
    
    
        return $codes;
    }

    /**
     * Get the default redirect url. Required if no returnTo parameter has been supplied.
     * This method is called in handleCommand so we know which command has been performed.
     *
     * @param array $args List of arguments
     *
     * @return string The default redirect url
     */
    protected function getDefaultReturnUrl($args)
    {
        $objectIsPersisted = $args['commandName'] != 'delete' && !($this->templateParameters['mode'] == 'create' && $args['commandName'] == 'cancel');
    
        if (null !== $this->returnTo) {
            $isDisplayOrEditPage = substr($this->returnTo, -7) == 'display' || substr($this->returnTo, -4) == 'edit';
            if (!$isDisplayOrEditPage || $objectIsPersisted) {
                // return to referer
                return $this->returnTo;
            }
        }
    
        $routeArea = array_key_exists('routeArea', $this->templateParameters) ? $this->templateParameters['routeArea'] : '';
        $routePrefix = 'zikularoutesmodule_' . $this->objectTypeLower . '_' . $routeArea;
    
        // redirect to the list of routes
        $url = $this->router->generate($routePrefix . 'view');
    
        return $url;
    }

    /**
     * Command event handler.
     *
     * This event handler is called when a command is issued by the user.
     *
     * @param array $args List of arguments
     *
     * @return mixed Redirect or false on errors
     */
    public function handleCommand($args = [])
    {
        $result = parent::handleCommand($args);
        if (false === $result) {
            return $result;
        }
    
        // build $args for BC (e.g. used by redirect handling)
        foreach ($this->templateParameters['actions'] as $action) {
            if ($this->form->get($action['id'])->isClicked()) {
                $args['commandName'] = $action['id'];
            }
        }
        if ($this->form->get('cancel')->isClicked()) {
            $args['commandName'] = 'cancel';
        }
    
        return new RedirectResponse($this->getRedirectUrl($args), 302);
    }
    
    /**
     * Get success or error message for default operations.
     *
     * @param array   $args    Arguments from handleCommand method
     * @param Boolean $success Becomes true if this is a success, false for default error
     *
     * @return String desired status or error message
     */
    protected function getDefaultMessage($args, $success = false)
    {
        if (false === $success) {
            return parent::getDefaultMessage($args, $success);
        }
    
        $message = '';
        switch ($args['commandName']) {
            case 'submit':
                if ($this->templateParameters['mode'] == 'create') {
                    $message = $this->__('Done! Route created.');
                } else {
                    $message = $this->__('Done! Route updated.');
                }
                break;
            case 'delete':
                $message = $this->__('Done! Route deleted.');
                break;
            default:
                $message = $this->__('Done! Route updated.');
                break;
        }
    
        return $message;
    }

    /**
     * This method executes a certain workflow action.
     *
     * @param array $args Arguments from handleCommand method
     *
     * @return bool Whether everything worked well or not
     *
     * @throws RuntimeException Thrown if concurrent editing is recognised or another error occurs
     */
    public function applyAction(array $args = [])
    {
        // get treated entity reference from persisted member var
        $entity = $this->entityRef;
    
        $action = $args['commandName'];
    
        $success = false;
        $flashBag = $this->request->getSession()->getFlashBag();
        try {
            // execute the workflow action
            $success = $this->workflowHelper->executeAction($entity, $action);
        } catch(\Exception $exception) {
            $flashBag->add('error', $this->__f('Sorry, but an error occured during the %action% action. Please apply the changes again!', ['%action%' => $action]) . ' ' . $exception->getMessage());
            $logArgs = ['app' => 'ZikulaRoutesModule', 'user' => $this->currentUserApi->get('uname'), 'entity' => 'route', 'id' => $entity->getKey(), 'errorMessage' => $exception->getMessage()];
            $this->logger->error('{app}: User {user} tried to edit the {entity} with id {id}, but failed. Error details: {errorMessage}.', $logArgs);
        }
    
        $this->addDefaultMessage($args, $success);
    
        if ($success && $this->templateParameters['mode'] == 'create') {
            // store new identifier
            foreach ($this->idFields as $idField) {
                $this->idValues[$idField] = $entity[$idField];
            }
        }
    
        return $success;
    }

    /**
     * Get url to redirect to.
     *
     * @param array $args List of arguments
     *
     * @return string The redirect url
     */
    protected function getRedirectUrl($args)
    {
        if ($this->repeatCreateAction) {
            return $this->repeatReturnUrl;
        }
    
        if ($this->request->getSession()->has('zikularoutesmodule' . $this->objectTypeCapital . 'Referer')) {
            $this->request->getSession()->del('zikularoutesmodule' . $this->objectTypeCapital . 'Referer');
        }
    
        // normal usage, compute return url from given redirect code
        if (!in_array($this->returnTo, $this->getRedirectCodes())) {
            // invalid return code, so return the default url
            return $this->getDefaultReturnUrl($args);
        }
    
        $routeArea = substr($this->returnTo, 0, 5) == 'admin' ? 'admin' : '';
        $routePrefix = 'zikularoutesmodule_' . $this->objectTypeLower . '_' . $routeArea;
    
        // parse given redirect code and return corresponding url
        switch ($this->returnTo) {
            case 'userIndex':
            case 'adminIndex':
                return $this->router->generate($routePrefix . 'index');
            case 'userView':
            case 'adminView':
                return $this->router->generate($routePrefix . 'view');
            case 'userOwnView':
            case 'adminOwnView':
                return $this->router->generate($routePrefix . 'view', [ 'own' => 1 ]);
            case 'userDisplay':
            case 'adminDisplay':
                if ($args['commandName'] != 'delete' && !($this->templateParameters['mode'] == 'create' && $args['commandName'] == 'cancel')) {
                    $urlArgs[$this->idField] = $this->idValue;
    
                    return $this->router->generate($routePrefix . 'display', $urlArgs);
                }
    
                return $this->getDefaultReturnUrl($args);
            default:
                return $this->getDefaultReturnUrl($args);
        }
    }
}
