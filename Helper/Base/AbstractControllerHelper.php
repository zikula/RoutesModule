<?php

declare(strict_types=1);

/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <info@ziku.la>.
 * @link https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

namespace Zikula\RoutesModule\Helper\Base;

use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\RoutesModule\Entity\Factory\EntityFactory;
use Zikula\RoutesModule\Helper\CollectionFilterHelper;
use Zikula\RoutesModule\Helper\ModelHelper;
use Zikula\RoutesModule\Helper\PermissionHelper;

/**
 * Helper base class for controller layer methods.
 */
abstract class AbstractControllerHelper
{
    use TranslatorTrait;
    
    /**
     * @var RequestStack
     */
    protected $requestStack;
    
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    
    /**
     * @var VariableApiInterface
     */
    protected $variableApi;
    
    /**
     * @var EntityFactory
     */
    protected $entityFactory;
    
    /**
     * @var CollectionFilterHelper
     */
    protected $collectionFilterHelper;
    
    /**
     * @var PermissionHelper
     */
    protected $permissionHelper;
    
    /**
     * @var ModelHelper
     */
    protected $modelHelper;
    
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        FormFactoryInterface $formFactory,
        VariableApiInterface $variableApi,
        EntityFactory $entityFactory,
        CollectionFilterHelper $collectionFilterHelper,
        PermissionHelper $permissionHelper,
        ModelHelper $modelHelper
    ) {
        $this->setTranslator($translator);
        $this->requestStack = $requestStack;
        $this->formFactory = $formFactory;
        $this->variableApi = $variableApi;
        $this->entityFactory = $entityFactory;
        $this->collectionFilterHelper = $collectionFilterHelper;
        $this->permissionHelper = $permissionHelper;
        $this->modelHelper = $modelHelper;
    }
    
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
    
    /**
     * Returns an array of all allowed object types in ZikulaRoutesModule.
     *
     * @return string[] List of allowed object types
     */
    public function getObjectTypes(string $context = '', array $args = []): array
    {
        if (!in_array($context, ['controllerAction', 'api', 'helper', 'actionHandler', 'block', 'contentType', 'util'], true)) {
            $context = 'controllerAction';
        }
    
        $allowedObjectTypes = [];
        $allowedObjectTypes[] = 'route';
    
        return $allowedObjectTypes;
    }
    
    /**
     * Returns the default object type in ZikulaRoutesModule.
     */
    public function getDefaultObjectType(string $context = '', array $args = []): string
    {
        if (!in_array($context, ['controllerAction', 'api', 'helper', 'actionHandler', 'block', 'contentType', 'util'], true)) {
            $context = 'controllerAction';
        }
    
        return 'route';
    }
    
    /**
     * Processes the parameters for a view action.
     * This includes handling pagination, quick navigation forms and other aspects.
     */
    public function processViewActionParameters(
        string $objectType,
        SortableColumns $sortableColumns,
        array $templateParameters = []
    ): array {
        $contextArgs = ['controller' => $objectType, 'action' => 'view'];
        if (!in_array($objectType, $this->getObjectTypes('controllerAction', $contextArgs), true)) {
            throw new Exception($this->__('Error! Invalid object type received.'));
        }
    
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new Exception($this->__('Error! Controller helper needs a request.'));
        }
        $repository = $this->entityFactory->getRepository($objectType);
    
        // parameter for used sorting field
        list ($sort, $sortdir) = $this->determineDefaultViewSorting($objectType);
        $templateParameters['sort'] = $sort;
        $templateParameters['sortdir'] = strtolower($sortdir);
    
        $templateParameters['all'] = 'csv' === $request->getRequestFormat() ? 1 : $request->query->getInt('all');
        $templateParameters['own'] = (bool)$request->query->getInt('own', $this->variableApi->get('ZikulaRoutesModule', 'showOnlyOwnEntries')) ? 1 : 0;
    
        $resultsPerPage = 0;
        if (1 !== $templateParameters['all']) {
            // the number of items displayed on a page for pagination
            $resultsPerPage = $request->query->getInt('num');
            if (in_array($resultsPerPage, [0, 10], true)) {
                $resultsPerPage = $this->variableApi->get('ZikulaRoutesModule', $objectType . 'EntriesPerPage', 10);
            }
        }
        $templateParameters['num'] = $resultsPerPage;
        $templateParameters['tpl'] = $request->query->getAlnum('tpl');
    
        $templateParameters = $this->addTemplateParameters($objectType, $templateParameters, 'controllerAction', $contextArgs);
    
        $quickNavForm = $this->formFactory->create('Zikula\RoutesModule\Form\Type\QuickNavigation\\' . ucfirst($objectType) . 'QuickNavType', $templateParameters);
        $quickNavForm->handleRequest($request);
        if ($quickNavForm->isSubmitted()) {
            $quickNavData = $quickNavForm->getData();
            foreach ($quickNavData as $fieldName => $fieldValue) {
                if ('routeArea' === $fieldName) {
                    continue;
                }
                if (in_array($fieldName, ['all', 'own', 'num'], true)) {
                    $templateParameters[$fieldName] = $fieldValue;
                } elseif ('sort' === $fieldName && !empty($fieldValue)) {
                    $sort = $fieldValue;
                } elseif ('sortdir' === $fieldName && !empty($fieldValue)) {
                    $sortdir = $fieldValue;
                } elseif (false === stripos($fieldName, 'thumbRuntimeOptions') && false === stripos($fieldName, 'featureActivationHelper') && false === stripos($fieldName, 'permissionHelper')) {
                    // set filter as query argument, fetched inside repository
                    $request->query->set($fieldName, $fieldValue);
                }
            }
        }
        $sortableColumns->setOrderBy($sortableColumns->getColumn($sort), strtoupper($sortdir));
        $resultsPerPage = $templateParameters['num'];
        $request->query->set('own', $templateParameters['own']);
    
        $urlParameters = $templateParameters;
        foreach ($urlParameters as $parameterName => $parameterValue) {
            if (false === stripos($parameterName, 'thumbRuntimeOptions')
                && false === stripos($parameterName, 'featureActivationHelper')
            ) {
                continue;
            }
            unset($urlParameters[$parameterName]);
        }
    
        $sortableColumns->setAdditionalUrlParameters($urlParameters);
    
        $where = '';
        if (1 === $templateParameters['all']) {
            // retrieve item list without pagination
            $entities = $repository->selectWhere($where, $sort . ' ' . $sortdir, false);
        } else {
            // the current offset which is used to calculate the pagination
            $currentPage = $request->query->getInt('pos', 1);
    
            // retrieve item list with pagination
            list($entities, $objectCount) = $repository->selectWherePaginated($where, $sort . ' ' . $sortdir, $currentPage, $resultsPerPage, false);
    
            $templateParameters['currentPage'] = $currentPage;
            $templateParameters['pager'] = [
                'amountOfItems' => $objectCount,
                'itemsPerPage' => $resultsPerPage
            ];
        }
    
        $templateParameters['sort'] = $sort;
        $templateParameters['sortdir'] = $sortdir;
        $templateParameters['items'] = $entities;
    
        $templateParameters['sort'] = $sortableColumns->generateSortableColumns();
        $templateParameters['quickNavForm'] = $quickNavForm->createView();
    
        $templateParameters['canBeCreated'] = $this->modelHelper->canBeCreated($objectType);
    
        $request->query->set('sort', $sort);
        $request->query->set('sortdir', $sortdir);
        // set current sorting in route parameters (e.g. for the pager)
        $routeParams = $request->attributes->get('_route_params');
        $routeParams['sort'] = $sort;
        $routeParams['sortdir'] = $sortdir;
        $request->attributes->set('_route_params', $routeParams);
    
        return $templateParameters;
    }
    
    /**
     * Determines the default sorting criteria.
     */
    protected function determineDefaultViewSorting(string $objectType): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return ['', 'ASC'];
        }
        $repository = $this->entityFactory->getRepository($objectType);
    
        $sort = $request->query->get('sort', '');
        if (empty($sort) || !in_array($sort, $repository->getAllowedSortingFields(), true)) {
            $sort = $repository->getDefaultSortingField();
            $request->query->set('sort', $sort);
            // set default sorting in route parameters (e.g. for the pager)
            $routeParams = $request->attributes->get('_route_params');
            $routeParams['sort'] = $sort;
            $request->attributes->set('_route_params', $routeParams);
        }
        $sortdir = $request->query->get('sortdir', 'ASC');
        if (false !== strpos($sort, ' DESC')) {
            $sort = str_replace(' DESC', '', $sort);
            $sortdir = 'desc';
        }
    
        return [$sort, $sortdir];
    }
    
    /**
     * Processes the parameters for a display action.
     */
    public function processDisplayActionParameters(string $objectType, array $templateParameters = []): array
    {
        $contextArgs = ['controller' => $objectType, 'action' => 'display'];
        if (!in_array($objectType, $this->getObjectTypes('controllerAction', $contextArgs), true)) {
            throw new Exception($this->__('Error! Invalid object type received.'));
        }
    
        return $this->addTemplateParameters($objectType, $templateParameters, 'controllerAction', $contextArgs);
    }
    
    /**
     * Processes the parameters for an edit action.
     */
    public function processEditActionParameters(string $objectType, array $templateParameters = []): array
    {
        $contextArgs = ['controller' => $objectType, 'action' => 'edit'];
        if (!in_array($objectType, $this->getObjectTypes('controllerAction', $contextArgs), true)) {
            throw new Exception($this->__('Error! Invalid object type received.'));
        }
    
        return $this->addTemplateParameters($objectType, $templateParameters, 'controllerAction', $contextArgs);
    }
    
    /**
     * Returns an array of additional template variables which are specific to the object type.
     */
    public function addTemplateParameters(string $objectType = '', array $parameters = [], string $context = '', array $args = []): array
    {
        if (!in_array($context, ['controllerAction', 'api', 'actionHandler', 'block', 'contentType', 'mailz'], true)) {
            $context = 'controllerAction';
        }
    
        if ('controllerAction' === $context) {
            if (!isset($args['action'])) {
                $routeName = $this->requestStack->getCurrentRequest()->get('_route');
                $routeNameParts = explode('_', $routeName);
                $args['action'] = end($routeNameParts);
            }
            if (in_array($args['action'], ['index', 'view'])) {
                $parameters = array_merge($parameters, $this->collectionFilterHelper->getViewQuickNavParameters($objectType, $context, $args));
            }
        }
        $parameters['permissionHelper'] = $this->permissionHelper;
    
        return $parameters;
    }
}
