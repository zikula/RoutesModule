<?php

/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <info@ziku.la>.
 *
 * @see https://ziku.la
 *
 * @version Generated by ModuleStudio 1.5.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Twig\Base;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zikula\RoutesModule\Twig\TwigRuntime;

/**
 * Twig extension base class.
 */
abstract class AbstractTwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('zikularoutesmodule_objectTypeSelector', [TwigRuntime::class, 'getObjectTypeSelector']),
            new TwigFunction('zikularoutesmodule_templateSelector', [TwigRuntime::class, 'getTemplateSelector']),
        ];
    }
    
    public function getFilters()
    {
        return [
            new TwigFilter('zikularoutesmodule_listEntry', [TwigRuntime::class, 'getListEntry']),
            new TwigFilter('zikularoutesmodule_formattedTitle', [TwigRuntime::class, 'getFormattedEntityTitle']),
            new TwigFilter('zikularoutesmodule_objectState', [TwigRuntime::class, 'getObjectState'], ['is_safe' => ['html']]),
        ];
    }
}
