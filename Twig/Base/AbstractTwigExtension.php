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

namespace Zikula\RoutesModule\Twig\Base;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\RoutesModule\Helper\ListEntriesHelper;
use Zikula\RoutesModule\Helper\WorkflowHelper;

/**
 * Twig extension base class.
 */
abstract class AbstractTwigExtension extends \Twig_Extension
{
    use TranslatorTrait;
    
    /**
     * @var VariableApi
     */
    protected $variableApi;
    
    /**
     * @var WorkflowHelper
     */
    protected $workflowHelper;
    
    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;
    
    /**
     * Constructor.
     * Initialises member vars.
     *
     * @param TranslatorInterface $translator     Translator service instance
     * @param VariableApi         $variableApi    VariableApi service instance
     * @param WorkflowHelper      $workflowHelper WorkflowHelper service instance
     * @param ListEntriesHelper   $listHelper     ListEntriesHelper service instance
     */
    public function __construct(TranslatorInterface $translator, VariableApi $variableApi, WorkflowHelper $workflowHelper, ListEntriesHelper $listHelper)
    {
        $this->setTranslator($translator);
        $this->variableApi = $variableApi;
        $this->workflowHelper = $workflowHelper;
        $this->listHelper = $listHelper;
    }
    
    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Returns a list of custom Twig functions.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('zikularoutesmodule_objectTypeSelector', [$this, 'getObjectTypeSelector']),
            new \Twig_SimpleFunction('zikularoutesmodule_templateSelector', [$this, 'getTemplateSelector']),
            new \Twig_SimpleFunction('zikularoutesmodule_userVar', [$this, 'getUserVar']),
            new \Twig_SimpleFunction('zikularoutesmodule_userAvatar', [$this, 'getUserAvatar'], ['is_safe' => ['html']])
        ];
    }
    
    /**
     * Returns a list of custom Twig filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('zikularoutesmodule_listEntry', [$this, 'getListEntry']),
            new \Twig_SimpleFilter('zikularoutesmodule_objectState', [$this, 'getObjectState'], ['is_safe' => ['html']])
        ];
    }
    
    /**
     * The zikularoutesmodule_objectState filter displays the name of a given object's workflow state.
     * Examples:
     *    {{ item.workflowState|zikularoutesmodule_objectState }}        {# with visual feedback #}
     *    {{ item.workflowState|zikularoutesmodule_objectState(false) }} {# no ui feedback #}
     *
     * @param string  $state      Name of given workflow state
     * @param boolean $uiFeedback Whether the output should include some visual feedback about the state
     *
     * @return string Enriched and translated workflow state ready for display
     */
    public function getObjectState($state = 'initial', $uiFeedback = true)
    {
        $stateInfo = $this->workflowHelper->getStateInfo($state);
    
        $result = $stateInfo['text'];
        if (true === $uiFeedback) {
            $result = '<span class="label label-' . $stateInfo['ui'] . '">' . $result . '</span>';
        }
    
        return $result;
    }
    
    
    /**
     * The zikularoutesmodule_listEntry filter displays the name
     * or names for a given list item.
     * Example:
     *     {{ entity.listField|zikularoutesmodule_listEntry('entityName', 'fieldName') }}
     *
     * @param string $value      The dropdown value to process
     * @param string $objectType The treated object type
     * @param string $fieldName  The list field's name
     * @param string $delimiter  String used as separator for multiple selections
     *
     * @return string List item name
     */
    public function getListEntry($value, $objectType = '', $fieldName = '', $delimiter = ', ')
    {
        if ((empty($value) && $value != '0') || empty($objectType) || empty($fieldName)) {
            return $value;
        }
    
        return $this->listHelper->resolve($value, $objectType, $fieldName, $delimiter);
    }
    
    
    /**
     * The zikularoutesmodule_objectTypeSelector function provides items for a dropdown selector.
     *
     * @return string The output of the plugin
     */
    public function getObjectTypeSelector()
    {
        $result = [];
    
        $result[] = ['text' => $this->__('Routes'), 'value' => 'route'];
    
        return $result;
    }
    
    
    /**
     * The zikularoutesmodule_templateSelector function provides items for a dropdown selector.
     *
     * @return string The output of the plugin
     */
    public function getTemplateSelector()
    {
        $result = [];
    
        $result[] = ['text' => $this->__('Only item titles'), 'value' => 'itemlist_display.html.twig'];
        $result[] = ['text' => $this->__('With description'), 'value' => 'itemlist_display_description.html.twig'];
        $result[] = ['text' => $this->__('Custom template'), 'value' => 'custom'];
    
        return $result;
    }
    
    /**
     * Returns the value of a user variable.
     *
     * @param string     $name    Name of desired property
     * @param int        $uid     The user's id
     * @param string|int $default The default value
     *
     * @return string
     */
    public function getUserVar($name, $uid = -1, $default = '')
    {
        if (!$uid) {
            $uid = -1;
        }
    
        $result = \UserUtil::getVar($name, $uid, $default);
    
        return $result;
    }
    
    /**
     * Display the avatar of a user.
     *
     * @param int    $uid    The user's id
     * @param int    $width  Image width (optional)
     * @param int    $height Image height (optional)
     * @param int    $size   Gravatar size (optional)
     * @param string $rating Gravatar self-rating [g|pg|r|x] see: http://en.gravatar.com/site/implement/images/ (optional)
     *
     * @return string
     */
    public function getUserAvatar($uid, $width = 0, $height = 0, $size = 0, $rating = '')
    {
        $params = ['uid' => $uid];
        if ($width > 0) {
            $params['width'] = $width;
        }
        if ($height > 0) {
            $params['height'] = $height;
        }
        if ($size > 0) {
            $params['size'] = $size;
        }
        if ($rating != '') {
            $params['rating'] = $rating;
        }
    
        include_once 'lib/legacy/viewplugins/function.useravatar.php';
    
        $view = \Zikula_View::getInstance('ZikulaRoutesModule');
        $result = smarty_function_useravatar($params, $view);
    
        return $result;
    }
}
