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

namespace Zikula\RoutesModule\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Configuration form type base class.
 */
abstract class AbstractConfigType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var array
     */
    protected $moduleVars;

    /**
     * ConfigType constructor.
     *
     * @param TranslatorInterface $translator  Translator service instance
     * @param object              $moduleVars  Existing module vars
     */
    public function __construct(
        TranslatorInterface $translator,
        $moduleVars
    ) {
        $this->setTranslator($translator);
        $this->moduleVars = $moduleVars;
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
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addListViewsFields($builder, $options);

        $builder
            ->add('save', SubmitType::class, [
                'label' => $this->__('Update configuration'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default',
                    'formnovalidate' => 'formnovalidate'
                ]
            ])
        ;
    }

    /**
     * Adds fields for list views fields.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addListViewsFields(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('routeEntriesPerPage', IntegerType::class, [
                'label' => $this->__('Route entries per page') . ':',
                'label_attr' => [
                    'class' => 'tooltips',
                    'title' => $this->__('The amount of routes shown per page')
                ],
                'help' => $this->__('The amount of routes shown per page'),
                'required' => false,
                'data' => isset($this->moduleVars['routeEntriesPerPage']) ? intval($this->moduleVars['routeEntriesPerPage']) : intval(10),
                'empty_data' => intval('10'),
                'attr' => [
                    'maxlength' => 255,
                    'title' => $this->__('Enter the route entries per page.') . ' ' . $this->__('Only digits are allowed.')
                ],'scale' => 0
            ])
        ;
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'zikularoutesmodule_config';
    }
}
